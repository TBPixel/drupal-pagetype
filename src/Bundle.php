<?php

namespace TBPixel\PageType;

use TBPixel\PageType\Model;
use Consolidation\AnnotatedCommand\Hooks\Dispatchers\ValidateHookDispatcher;


class Bundle extends Model
{
    const TABLE      = 'page_types';
    const HOOK_BUILD = 'pagetype_info';


    /**
     * @var string
     */
    protected $machine_name;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $plural;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var int
     */
    protected $has_continuity;



    public function __construct(string $machine_name, string $name)
    {
        $this->setMachineName($machine_name);
        $this->setName($name);
        $this->setPlural($name);
        $this->setDescription('');
        $this->setHasContinuity(false);
    }



    public function setMachineName(string $machine_name) : void
    {
        if (strlen($machine_name) > 32) return;


        $this->machine_name = $machine_name;
    }


    public function setName(string $name) : void
    {
        if (strlen($name) > 255) return;


        $this->name = $name;
    }


    public function setPlural(string $plural) : void
    {
        if (strlen($plural) > 255) return;


        $this->plural = $plural;
    }


    public function setDescription(string $description) : void
    {
        $this->description = $description;
    }


    public function setHasContinuity(bool $is_reusable) : void
    {
        $this->has_continuity = (int) $is_reusable;
    }


    /**
     * 
     */
    public static function all() : array
    {
        return db_select(static::TABLE)
            ->fields(static::TABLE)
            ->execute()->fetchAllAssoc('machine_name', \PDO::FETCH_ASSOC);
    }



    /**
     * Returns an array of Bundle instances, built from module invokations and database pages
     */
    public static function build() : array
    {
        $types = static::all();
        $types += module_invoke_all(static::HOOK_BUILD);

        $types = array_map(
            function(array $type)
            {
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
        foreach ($types as $type)
        {
            if (!static::find($type->machine_name)) $type->save();
        }


        return $types;
    }


    /**
     * Accepts an array of info values for the type and returns with optional defaults, if not set
     */
    public static function defaults(array $type) : array
    {
        $type += [
            'name'              => '',
            'description'       => '',
            'has_continuity'    => 0,
        ];

        $type += [
            'plural' => $type['name']
        ];


        return $type;
    }


    /**
     * Find a bundle by name
     */
    public static function find(string $machine_name) : ?Bundle
    {
        $query = db_select(static::TABLE);
        $query->fields(static::TABLE);
        $query->condition('machine_name', $machine_name);
        $result = $query->execute()->fetch();

        if (!$result) return null;


        $bundle = new static($result->machine_name, $result->name, $result->description);
        $bundle->setHasContinuity($result->has_continuity);


        return $bundle;
    }


    /**
     * Deletes a bundle by it's machine_name
     */
    public static function delete(string $machine_name) : void
    {
        db_delete(static::TABLE)
            ->condition('machine_name', $machine_name)
            ->execute();
    }



    /**
     * Saves the current instance to the database
     */
    public function save() : Bundle
    {        
        if (!static::find($this->machine_name))
        {
            $query = db_insert(static::TABLE);
            $query->fields([
                'machine_name'   => $this->machine_name,
                'name'           => $this->name,
                'plural'         => $this->plural,
                'description'    => $this->description,
                'has_continuity' => $this->has_continuity
            ]);
            $query->execute();
        }
        else
        {
            $query = db_update(static::TABLE);
            $query->fields([
                'name'           => $this->name,
                'plural'         => $this->plural,                
                'description'    => $this->description,
                'has_continuity' => $this->has_continuity
            ]);
            $query->condition('machine_name', $this->machine_name);
            $query->execute();
        }


        return $this;
    }


    /**
     * Returns the URI of the given Bundle
     */
    public function uri() : string
    {
        return str_replace('_', '-', $this->machine_name);
    }
}
