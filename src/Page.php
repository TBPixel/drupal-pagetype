<?php

namespace TBPixel\PageType;

use TBPixel\PageType\Model;
use Throwable;
use EntityFieldQuery;
use DateTimeInterface;
use DateTime;


class Page extends Model
{
    const TABLE = 'pages';

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
     * @var DateTimeInterface
     */
    protected $created;

    /**
     * @var string
     */
    protected $status;



    public function __construct(string $type)
    {
        $this->setType($type);
        $this->setCreated(new DateTime);
        $this->setStatus('unpublished');
    }



    public static function find(array $ids) : array
    {
        if (empty($ids)) return [];

        $results = entity_load('page', $ids);


        return array_map(
            function($page)
            {
                $static = new static($page->type);

                if ($page->id)      $static->setId($page->id);
                if ($page->title)   $static->setTitle($page->title);
                if ($page->created) $static->setCreated(new DateTime("@{$page->created}"));
                if ($page->status)  $static->setStatus($page->status);

                foreach ($page as $key => $value)
                {
                    if (!property_exists($static, $key)) $static->{$key} = $value;
                }

                return $static;
            },
            $results
        );
    }


    public static function findOne(int $id) : ?Page
    {
        $results = static::find([$id]);


        return ($page = reset($results)) ? $page : null;
    }


    public function save() : Page
    {
        $transaction = db_transaction();


        try
        {
            field_attach_presave('page', $this);

            $is_new     = (!$this->id);
            $operation  = $is_new ? 'insert' : 'update';

            if ($is_new)
            {
                $this->setCreated(
                    new DateTime('@' . REQUEST_TIME)
                );
            }

            // Let modules modify the node before it is saved to the database.
            module_invoke_all('page_presave', $this);
            module_invoke_all('entity_presave', $this, 'page');


            if ($is_new)
            {
                $query = db_insert(static::TABLE);
                $query->fields([
                    'type'      => $this->type,
                    'title'     => $this->title,
                    'created'   => $this->created->getTimestamp(),
                    'status'    => $this->status
                ]);
                $this->id = $query->execute();

                field_attach_insert('page', $this);
            }
            else
            {
                $query = db_update(static::TABLE);
                $query->fields([
                    'type'      => $this->type,
                    'title'     => $this->title,
                    'created'   => $this->created->getTimestamp(),
                    'status'    => $this->status
                ]);
                $query->condition('id', $this->id);
                $query->execute();

                field_attach_update('page', $this);
            }


            module_invoke_all("page_{$operation}", $this);
            module_invoke_all("entity_{$operation}", $this, 'page');


            entity_get_controller('page')->resetCache([$this->id]);


            db_ignore_slave();
        }
        catch (Throwable $exception)
        {
            $transaction->rollback();

            watchdog_exception('pagetype', $exception);


            throw $exception;
        }


        return $this;
    }


    public function uri() : string
    {
        return drupal_get_path_alias("pages/{$this->id}");
    }


    public function setId(int $id) : void
    {
        if ($id < 0) return;

        $this->id = $id;
    }


    public function setTitle(string $title) : void
    {
        $this->title = $title;
    }


    public function setType(string $type) : void
    {
        if (strlen($type) > 32) return;


        $this->type = $type;
    }


    public function setCreated(DateTimeInterface $created) : void
    {
        $this->created = $created;
    }


    public function setStatus(string $status) : void
    {
        if (strlen($status) > 32) return;


        $this->status = $status;
    }
}
