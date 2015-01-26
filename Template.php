<?php
    namespace Nature;
    class Template {
        private $root;
        private $values=[];
        function __setup($configure){
            $this->root = $configure['root'];
            set_include_path(get_include_path().PATH_SEPARATOR.$this->root.PATH_SEPARATOR.ROOT.'/template');
        }
        function assign($key, $value=null){
            if(is_array($key)) {
                $this->values = array_merge($this->values, $key);
            } else {
                $this->values[$key] = $value;
            }
        }
        function get_template_filename($file=null){
            $dir = dirname($_SERVER['SCRIPT_NAME']);
            if(is_null($file)){
                $file = basename($_SERVER['SCRIPT_NAME'], '.php').'.html';
            }
            return $dir.'/'.$file;
        }
        function exists($file=null){
            if(is_null($file)) {
                $file = $this->get_template_path($file);
            }
            if (DEBUG) {
                clearstatcache();
            }
            return stream_resolve_include_path($file);
        }
        function display($file=null){
            if(!$this->exists($file)) {
                $file = $this->get_template_path($file);
                throw new \Exception("Template:<code>{$file}</code> Not Found");
            }
            extract($this->values);
            //include_once(__DIR__.'/template.function.php');
            require($file);
        }
    }