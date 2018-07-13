<?php

namespace TBPixel\PageType;

use Exception;
use DateTimeInterface;
use DateTime;

/**
 * Data model to represent a pagetype entity.
 */
class Page extends Model {
  const TABLE       = 'pages';
  const ENTITY_NAME = 'pagetype';

  /**
   * @var int
   */
  protected $id;

  /**
   * @var string
   */
  protected $type;

  /**
   * @var string
   */
  protected $title;

  /**
   * @var \DateTimeInterface
   */
  protected $created;

  /**
   * @var string
   */
  protected $status;

  /**
   * @var string
   */
  protected $language;

  /**
   * Constructor.
   */
  public function __construct(string $type) {
    $this->setType($type);
    $this->setCreated(new DateTime());
    $this->setStatus('unpublished');
    $this->language = ($langcode = entity_language('pagetype', $this)) ? $langcode : LANGUAGE_NONE;
  }

  /**
   * DB request to find Pages by an array of IDs.
   */
  public static function find(array $ids) : array {
    foreach ($ids as $key => $id) {
      if ($id === FALSE || $id === NULL) {
        unset($ids[$key]);
      }
    }

    if (empty($ids)) {
      return [];
    }

    $results = entity_load(static::ENTITY_NAME, $ids);

    return static::mapDbResults($results);
  }

  /**
   * DB Request to find Pages by a singular ID.
   */
  public static function findOne(int $id) : ?Page {
    $results = static::find([$id]);

    return ($page = reset($results)) ? $page : NULL;
  }

  /**
   * DB Request to find Pages by an array of conditions.
   */
  public static function findBy(array $conditions) : array {
    $query = db_select(static::TABLE);
    $query->fields(static::TABLE, ['id']);

    foreach ($conditions as $field => $value) {
      $query->condition($field, $value);
    }

    $results = $query->execute()->fetchCol();
    $results = reset($results);

    return static::find([$results]);
  }

  /**
   * DB Request to find a single result by an array of conditions.
   */
  public static function findOneBy(array $conditions) : ?Page {
    $results = static::findBy($conditions);

    return ($page = reset($results)) ? $page : NULL;
  }

  /**
   * DB Request to save the current Page object to the DB.
   * Performs either a create or update statement.
   */
  public function save() : Page {
    $transaction = db_transaction();

    try {
      field_attach_presave(static::ENTITY_NAME, $this);

      $is_new    = (!$this->id);
      $operation = $is_new ? 'insert' : 'update';

      if ($is_new) {
        $this->setCreated(
        new DateTime('@' . REQUEST_TIME)
        );
      }

      // Let modules modify the node before it is saved to the database.
      module_invoke_all(static::ENTITY_NAME . '_presave', $this);
      module_invoke_all('entity_presave', $this, static::ENTITY_NAME);

      if ($is_new) {
        $query = db_insert(static::TABLE);
        $query->fields([
          'type'      => $this->type,
          'title'     => $this->title,
          'created'   => $this->created->getTimestamp(),
          'status'    => $this->status,
        ]);
        $this->id = $query->execute();

        field_attach_insert(static::ENTITY_NAME, $this);
      }
      else {
        $query = db_update(static::TABLE);
        $query->fields([
          'type'      => $this->type,
          'title'     => $this->title,
          'created'   => $this->created->getTimestamp(),
          'status'    => $this->status,
        ]);
        $query->condition('id', $this->id);
        $query->execute();

        field_attach_update(static::ENTITY_NAME, $this);
      }

      module_invoke_all(static::ENTITY_NAME . "_{$operation}", $this);
      module_invoke_all("entity_{$operation}", $this, static::ENTITY_NAME);

      entity_get_controller(static::ENTITY_NAME)->resetCache([$this->id]);

      db_ignore_slave();
    }
    catch (Exception $exception) {
      $transaction->rollback();

      watchdog_exception('pagetype', $exception);

      throw $exception;
    }

    return $this;
  }

  /**
   * DB Request to delete the current Page.
   */
  public function delete() : Page {
    if (!$this->id) {
      return $this;
    }

    $transaction = db_transaction();

    try {
      module_invoke_all(static::ENTITY_NAME . '_delete', $this);
      module_invoke_all('entity_delete', $this, static::ENTITY_NAME);
      field_attach_delete(static::ENTITY_NAME, $this);

      db_delete(static::TABLE)
        ->condition('id', $this->id)
        ->execute();
    }
    catch (Exception $exception) {
      $transaction->rollback();

      watchdog_exception('pagetype', $exception);

      throw $exception;
    }

    return $this;
  }

  /**
   * Getter for the Page uri.
   */
  public function uri() : string {
    return drupal_get_path_alias("pages/{$this->id}");
  }

  /**
   * Sets the ID of the Page.
   */
  public function setId(int $id) : void {
    if ($id < 0) {
      return;
    }

    $this->id = $id;
  }

  /**
   * Sets teh title of the Page.
   */
  public function setTitle(string $title) : void {
    $this->title = $title;
  }

  /**
   * Sets the bundle of the Page.
   */
  public function setType(string $type) : void {
    if (strlen($type) > 32) {
      return;
    }

    $this->type = $type;
  }

  /**
   * Sets the created-at date of the Page.
   */
  public function setCreated(DateTimeInterface $created) : void {
    $this->created = $created;
  }

  /**
   * Sets the status of the page eg:
   *  - 'published'
   *  - 'unpublished'.
   */
  public function setStatus(string $status) : void {
    if (strlen($status) > 32) {
      return;
    }

    $this->status = $status;
  }

  /**
   * Maps entity_load database results to Page model.
   */
  public static function mapDbResults(array $results) : array {
    return array_map(
        function ($page) {
            $static = new static($page->type);

          if ($page->id) {
            $static->setId($page->id);
          }
          if ($page->title) {
            $static->setTitle($page->title);
          }
          if (is_numeric($page->created)) {
            $static->setCreated(new DateTime("@{$page->created}"));
          }
          elseif ($page->created instanceof DateTimeInterface) {
            $static->setCreated($page->created);
          }
          if ($page->status) {
            $static->setStatus($page->status);
          }

          foreach ($page as $key => $value) {
            if (!property_exists($static, $key)) {
              $static->{$key} = $value;
            }
          }

            return $static;
        },
        $results
    );
  }

}
