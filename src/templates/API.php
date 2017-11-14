<?php echo '<?php'; ?>


namespace <?php echo $namespace; ?>;

use Exception;

use Propel\Runtime\ActiveRecord\ActiveRecordInterface;
use Propel\Runtime\ActiveQuery\ModelCriteria;

/**
 * Class API is a static class for handling API requests generated by PropelJS.
 * 
 * Do not edit this file; any changes will be overwritten the next time you
 * run `propel model:build`.
 *
 * See the documentation at https://github.com/AthensFramework/PropelJS
 */
class API {
    
    /** @var string */
    static $method;
    
    /** @var integer */
    static $resourceId;
    
    /** @var string */
    static $resourceName;

    /** @var string */
    static $action;

    /** @var ModelCriteria */
    static $query;
    
    /** @var ActiveRecordInterface */
    static $resource;

    /** @var array */
    static $data;

    const ACTION_CREATE = 'CREATE';
    const ACTION_LIST = 'LIST';
    const ACTION_RETRIEVE = 'RETRIEVE';
    const ACTION_UPDATE = 'UPDATE';
    const ACTION_DESTROY = 'DESTROY';

    /**
    * @return string
    */
    protected static function getLocation()
    {
        return strtok($_SERVER['REQUEST_URI'], '?');
    }

    /**
     * @return string
     */
    public static function getMethod()
    {
        if (static::$method === null) {
            static::$method = $_SERVER['REQUEST_METHOD'];
        }
        return static::$method;
    }

    /**
     * @return string
     */
    public static function getResourceName()
    {
        if (static::$resourceName === null) {
            $path = explode('/', trim(static::getLocation(), '/'));
            
            if (is_numeric(end($path)) === true) {
                array_pop($path);
            }
            
            static::$resourceName = array_pop($path);
        }
        return static::$resourceName;
    }

    /**
     * @return integer
     */
    public static function getResourceId()
    {
        if (static::$resourceId === null) {
            $path = explode('/', trim(static::getLocation(), '/'));
            static::$resourceId = is_numeric(end($path)) === true ? array_pop($path) : null;
        }
        return static::$resourceId;
    }

    /**
     * @return array
     */
    public static function getData()
    {
        if (static::$data === null) {
            static::$data = json_decode(file_get_contents("php://input"), true);
        }
        return static::$data;
    }

    /**
    * @return string[]
    */
    public static function getQueryData()
    {
        return $_GET;
    }

    /**
     * @return string
     */
    public static function getAction()
    {
        $method = static::getMethod();

        $resourceId = static::getResourceId();

        if (static::$action === null) {
            if ($method === 'GET' && $resourceId !== null) {
                static::$method = static::ACTION_RETRIEVE;
            } elseif ($method === 'GET') {
                static::$method = static::ACTION_LIST;
            } elseif ($method === 'POST' && $resourceId === null) {
                static::$method = static::ACTION_CREATE;
            } elseif ($method === 'PUT' && $resourceId !== null) {
                static::$method = static::ACTION_UPDATE;
            } elseif ($method === 'DELETE' && $resourceId !== null) {
                static::$method = static::ACTION_DESTROY;
            } else {
                header("HTTP/1.0 405 Method Not Allowed");
                die("The method \"$method\" is not allowed, or is not allowed on this resource.");
            }
        }

        return static::$method;
    }

    /**
     * @return ModelCriteria
     */
    public static function getQueryOr404()
    {
        if (static::$query === null) {
            $resourceName = static::getResourceName();

            switch ($resourceName) {<?php foreach ($tablePlurals as $tableName => $tablePluralName) { ?>

                case '<?php echo $tablePluralName; ?>':
                    static::$query = <?php echo $tablePhpNames[$tableName]; ?>Query::create();
                    break;
<?php } ?>

                default:
                    header("HTTP/1.0 404 Not Found");
                    die("Resource type \"$resourceName\" not found.");
            }
        }

        return static::$query;
    }

    /**
     * @param ModelCriteria $query
     * @param $resourceId
     * @return ActiveRecordInterface
     */
    protected static function getResourceOr404(ModelCriteria $query, $resourceId)
    {
        if (static::$resource === null) {
            static::$resource = $query->findOneById($resourceId);
            if (static::$resource === null) {
                header("HTTP/1.0 404 Not Found");
                die("Resource id \"$resourceId\" not found.");
            }
        }

        return static::$resource;
    }

    /**
     * @param ModelCriteria $query
     * @param $data
     * @return string
     */
    protected static function createResource(ModelCriteria $query, $data)
    {
        /** @var string $className */
        $className = $query->getTableMap()->getClassName();

        /** @var ActiveRecordInterface $resource */
        $resource = new $className();

        $resource->fromArray($data);
        $resource->save();

        return json_encode($resource->toArray());
    }

    /**
    * @param ModelCriteria $query
    * @return string
    */
    protected static function listResources(ModelCriteria $query)
    {
        $queryData = static::getQueryData();
        
        foreach ($queryData as $columnName => $value) {
            if ($query->getTableMap()->hasColumnByPhpName($columnName) && trim($value) !== '') {
                $query->filterBy($columnName, $value);
            }
        }
        
        $queryData = array_merge(['offset' => 0, 'limit' => 25], $queryData);
        
        $query->offset($queryData['offset'])->limit($queryData['limit']);
        
        $queryDataNext = $queryData;
        $queryDataNext['offset'] += $queryData['limit'];
        
        $queryDataPrevious = $queryData;
        $queryDataPrevious['offset'] = max($queryData['offset'] - $queryData['limit'], 0);
        
        return json_encode(
            [
                'current' => static::getLocation() . '?' . http_build_query($queryData),
                'previous' => (int)$queryData['offset'] === 0 ?
                    null :
                    static::getLocation() . '?' . http_build_query($queryDataPrevious),
                'next' => static::getLocation() . '?' . http_build_query($queryDataNext),
                'data' => $query->find()->toArray()
            ]
            , JSON_UNESCAPED_SLASHES
        );
    }

    /**
     * Retrieves the given $resourceId within the given $query. Returns a JSON encoded
     * string.
     *
     * @param ModelCriteria $query
     * @param integer $resourceId
     * @return string
     */
    protected static function retrieveResource(ModelCriteria $query, $resourceId)
    {
        $resource = static::getResourceOr404($query, $resourceId);

        return json_encode($resource->toArray());
    }

    /**
     * Performs the update action on the given $resourceId within the given $query, using
     * the given $data. Returns a JSON encoded string.
     *
     * The array $data shall be an associative array of resource attribute key => value
     * pairs.
     *
     * @param ModelCriteria $query
     * @param integer       $resourceId
     * @param array         $data
     * @return string
     */
    protected static function updateResource(ModelCriteria $query, $resourceId, $data)
    {
        $resource = static::getResourceOr404($query, $resourceId);
        $resource->fromArray($data);
        $resource->save();

        return json_encode($resource->toArray());
    }

    /**
     * Performs the delete action on the given $resourceId within the given $query.
     *
     * @param ModelCriteria $query
     * @param $resourceId
     * @return string
     */
    protected static function deleteResource(ModelCriteria $query, $resourceId)
    {
        $resource = static::getResourceOr404($query, $resourceId);
        $resource->delete();

        return "{}";
    }

    /**
     * Handle the resource request identified by variables in $_SERVER.
     *
     * @return string
     * @throws Exception if ::getAction() identifies an unanticipated action.
     */
    public static function handle() {

        $resourceId = static::getResourceId();
        $query = static::getQueryOr404();
        $data = static::getData();

        switch (static::getAction()) {
            case static::ACTION_LIST:
                return static::listResources($query);
            case static::ACTION_CREATE:
                return static::createResource($query, $data);
            case static::ACTION_RETRIEVE:
                return static::retrieveResource($query, $resourceId);
            case static::ACTION_UPDATE:
                return static::updateResource($query, $resourceId, $data);
            case static::ACTION_DESTROY:
                return static::deleteResource($query, $resourceId);
            default:
                throw new Exception("Encountered unexpected action type \"{static::getAction}\" in ::handle.");
        }
    }
}


