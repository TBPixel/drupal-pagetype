<?php

namespace TBPixel\PageType;

/**
 * Class to represent a bundle used by the PageType module.
 */
class Bundle extends Model {
  const TABLE      = 'page_types';
  const HOOK_BUILD = 'pagetype_info';


  /**
   * Machine name of the Bundle.
   *
   * @var string
   */
  protected $machineName;

  /**
   * Human readable name of the Bundle.
   *
   * @var string
   */
  protected $name;

  /**
   * Pluralized name of the Bundle.
   *
   * @var string
   */
  protected $plural;

  /**
   * Description of the Bundle.
   *
   * @var string
   */
  protected $description;

  /**
   * Whether the bundle has continuity or not.
   *
   * @var int
   */
  protected $hasContinuity;

  /**
   * Constructor.
   */
  public function __construct(string $machineName, string $name) {
    $this->setMachineName($machineName);
    $this->setName($name);
    $this->setPlural($name);
    $this->setDescription('');
    $this->setHasContinuity(FALSE);
  }

  /**
   * Sets the machine name of the bundle.
   */
  public function setMachineName(string $machineName) : void {
    if (strlen($machineName) > 32) {
      return;
    }

    $this->machineName = $machineName;
  }

  /**
   * Sets the human readable name of the bundle.
   */
  public function setName(string $name) : void {
    if (strlen($name) > 255) {
      return;
    }

    $this->name = $name;
  }

  /**
   * Sets the pluralized human readable name of the bundle.
   */
  public function setPlural(string $plural) : void {
    if (strlen($plural) > 255) {
      return;
    }

    $this->plural = $plural;
  }

  /**
   * Sets the description of the bundle.
   */
  public function setDescription(string $description) : void {
    $this->description = $description;
  }

  /**
   * Sets whether this bundle has continuity.
   */
  public function setHasContinuity(bool $is_reusable) : void {
    $this->hasContinuity = (int) $is_reusable;
  }

  /**
   * Fetches all bundles from the database.
   */
  public static function all() : array {
    return db_select(static::TABLE)
      ->fields(static::TABLE)
      ->execute()->fetchAllAssoc('machine_name', \PDO::FETCH_ASSOC);
  }

  /**
   * Returns an array of Bundle instances, built from module invokations and database pages.
   */
  public static function build() : array {
    $types = static::all();
    $types += module_invoke_all(static::HOOK_BUILD);

    $types = array_map(
        function (array $type) {
            $type = static::defaults($type);

            $bundle = new static(
                $type['machine_name'],
                $type['name']
            );

            $bundle->setPlural($type['plural']);
            $bundle->setDescription($type['description']);
            $bundle->setHasContinuity($type['has_continuity']);

            return $bundle;
        },
        $types
    );

    /** @var Bundle $type */
    foreach ($types as $type) {
      if (!static::find($type->machineName)) {
        $type->save();
      }
    }

    return $types;
  }

  /**
   * Accepts an array of info values for the type and returns with optional defaults, if not set.
   */
  public static function defaults(array $type) : array {
    $type += [
      'name'              => '',
      'description'       => '',
      'has_continuity'    => 0,
    ];

    $type += [
      'plural' => $type['name'],
    ];

    return $type;
  }

  /**
   * Find a bundle by name.
   */
  public static function find(string $machineName) : ?Bundle {
    $query = db_select(static::TABLE);
    $query->fields(static::TABLE);
    $query->condition('machine_name', $machineName);
    $result = $query->execute()->fetch();

    if (!$result) {
      return NULL;
    }

    $bundle = new static($result->machineName, $result->name, $result->description);
    $bundle->setHasContinuity($result->hasContinuity);

    return $bundle;
  }

  /**
   * Deletes a bundle by it's machine_name.
   */
  public static function delete(string $machineName) : void {
    db_delete(static::TABLE)
      ->condition('machine_name', $machineName)
      ->execute();
  }

  /**
   * Saves the current instance to the database.
   */
  public function save() : Bundle {
    if (!static::find($this->machineName)) {
      $query = db_insert(static::TABLE);
      $query->fields([
        'machineName'   => $this->machineName,
        'name'           => $this->name,
        'plural'         => $this->plural,
        'description'    => $this->description,
        'has_continuity' => $this->hasContinuity,
      ]);
      $query->execute();
    }
    else {
      $query = db_update(static::TABLE);
      $query->fields([
        'name'           => $this->name,
        'plural'         => $this->plural,
        'description'    => $this->description,
        'has_continuity' => $this->hasContinuity,
      ]);
      $query->condition('machine_name', $this->machineName);
      $query->execute();
    }

    return $this;
  }

  /**
   * Returns the URI of the given Bundle.
   */
  public function uri() : string {
    return str_replace('_', '-', $this->machineName);
  }

}
