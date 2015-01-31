<?php
    namespace Nature;
    define('VERSION', '0.0.2');
    define('VERSION_NAME', 'WestLake');
    define('ROOT', __DIR__);
    /**
     * nature library 核心类
     */
    require_once __DIR__.'/nature.function.php';
    class App 
    {
        static $configure=array();
        function __construct($app_dir=null) 
        {
            if (is_null($app_dir)) {
                //decide by comporser's structure
                $app_dir = realpath(ROOT.'/../../../');
            }
            define('APP_DIR', $app_dir);
            set_include_path(get_include_path().':'.ROOT);
            $this->load_config();
            set_exception_handler(array($this, 'exception_handler'));
            //set_error_handler([$this, 'error_handler']);
            define('DEBUG', configure('debug'));
            $this->power();
        }
        function run() 
        {
            $this->call_controller();
            $this->call_function();
        }
        /**
         * 异常处理程序
         */
        function exception_handler($exception)
        {
            if(!is_a($exception, 'Nature\HTTPException')) {
                http_response_code(500);
                $tpl = singleton('tpl');
                $tpl->assign('exception', $exception);
                $tpl->assign(array(
                    'errno'=>$exception->getCode(),
                    'errstr'=>$exception->getMessage(),
                    'errfile'=>$exception->getFile(),
                    'errline'=>$exception->getLine()
                ));
                $tpl->display('500.html');
            }
        }
        /**
         * 解析配置程序
         */
        function parse_config($configure, &$position)
        {
            if(!is_array($configure)) {
                return null;
            }
            foreach($configure as $key=>$value){
                $pointer = &$position;
                $keys = explode('.', $key);
                $key = array_pop($keys);
                foreach($keys as $item){
                    $pointer = &$pointer[$item];
                }
                if(is_array($value)) {
                    self::parse_config($value, $pointer[$key]);
                } else {
                    $pointer[$key] = $value;
                }
            }
        }
        /**
         * 加载配置文件
         */
        function load_config()
        {
            self::parse_config(require(__DIR__.'/configure.php'), self::$configure);
            if(file_exists(APP_DIR.'/configure.php')) {
                self::parse_config(include(APP_DIR.'/configure.php'), self::$configure);
            }
            return self::$configure;
        }
        function rest($object=null)
        {
            $method = strtolower($_SERVER['REQUEST_METHOD']);
            $types = array(
                'post'=>$_POST,
                'get'=>$_GET,
                'delete'=>$_REQUEST,
                'put'=>$_REQUEST
            );
            $params = $types[$method];
            if(!is_null($object)) {
                $method = array($object, $method);
            }
            if(is_callable($method)){
                $returnData = call_user_func($method, $params);
                
                switch (gettype($returnData)) {
                    case 'array':
                        echo json_encode($returnData);
                        break;
                    case 'string':
                    case 'integer':
                    case 'float':
                    case 'double':
                        echo $returnData;
                        break;
                }
                
            }
        }
        /**
         * alias of rest
         */
        function call_function()
        {
            $this->rest();
        }
        function call_controller() 
        {
            foreach (get_declared_classes() as $class) {
                $reflection = new \ReflectionClass($class);
                if($reflection->isSubclassOf('Nature\\Controller') && !$reflection->isAbstract()) {
                    $obj = $reflection->newInstance();
                    $this->rest($obj);
                }
            }
        }
        /**
         * power by information
         */
        function power()
        {
	        if (configure('x-powered-by')) {
		       header('X-Powered-By: Nature/'.VERSION.' ('.VERSION_NAME.')'); 
	        }
        }
    }