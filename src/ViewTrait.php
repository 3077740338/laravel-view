<?php
/*
|----------------------------------------------------------------------------
| TopWindow [ Internet Ecological traffic aggregation and sharing platform ]
|----------------------------------------------------------------------------
| Copyright (c) 2006-2019 http://yangrong1.cn All rights reserved.
|----------------------------------------------------------------------------
| Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
|----------------------------------------------------------------------------
| Author: yangrong <yangrong2@gmail.com>
|----------------------------------------------------------------------------
*/
namespace Learn\View;

use Illuminate\Http\Response;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Foundation\Application;
trait ViewTrait
{
    /**
     * 应用实例
     * @var \Illuminate\Contracts\Foundation\Application 
     */
    protected $app;
    /**
     * 视图类实例
     * @var \Illuminate\Contracts\View\Factory
     */
    protected $view;
    /**
     * 当前模板变量
     * @var array
     */
    protected $__data = [];
    /**
     * 视图输出过滤
     * @var \Closure[]
     */
    protected $filters = [];
    /**
     * Object Oriented
     * 
     * @param  \Illuminate\Contracts\Foundation\Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->view = $app['view'];
    }
    /**
     * 写入静态模板缓存用于页面展示(运营模式)
     *
     * @param  mixed $html 缓存值
     * @return bool|void
     */
    protected function writeHtmlCache($html = '')
    {
    }
    /**
     * 读取静态模板缓存用于页面展示(运营模式)
     * 
     * @return mixed
     *
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    protected function readHtmlCache()
    {
    }
    /**
     * 删除静态模板缓存(调试模式)
     * 
     * @return bool
     */
    protected function deleteHtmlCache()
    {
    }
    /**
     * 检测是否存在模板文件
     * 
     * @param  string $template 模板文件或者模板规则
     * @return bool
     */
    protected function exists($template)
    {
        return $this->view->exists($template);
    }
    /**
     * 获取给定列表中实际存在的第一个视图
     * 
     * @param  array  $views
     * @param  \Illuminate\Contracts\Support\Arrayable|array  $data
     * @param  array  $mergeData
     * @return \Illuminate\Http\Response
     *
     * @throws \InvalidArgumentException
     */
    protected function first(array $views, $data = [], $mergeData = [])
    {
        $data = array_merge($this->__data, $this->parseData($data));
        return $this->getContent(function ($ViewFactory) use($views, $data, $mergeData) {
            return $ViewFactory->first($views, $data, $mergeData)->render();
        });
    }
    /**
     * 基于给定条件获取视图的渲染内容
     * 
     * @param  bool  $condition
     * @param  string  $view
     * @param  \Illuminate\Contracts\Support\Arrayable|array  $data
     * @param  array  $mergeData
     * @return \Illuminate\Http\Response
     */
    protected function renderWhen($condition, $view, $data = [], $mergeData = [])
    {
        $data = array_merge($this->__data, $this->parseData($data));
        return $this->getContent(function ($ViewFactory) use($condition, $view, $data, $mergeData) {
            return $ViewFactory->renderWhen($condition, $view, $data, $mergeData);
        });
    }
    /**
     * 从循环中获取片段的呈现内容
     * 
     * @param  string  $view
     * @param  array  $data
     * @param  string  $iterator
     * @param  string  $empty
     * @return \Illuminate\Http\Response
     */
    protected function renderEach($view, $data, $iterator, $empty = 'raw|')
    {
        $data = array_merge($this->__data, $this->parseData($data));
        return $this->getContent(function ($ViewFactory) use($view, $data, $iterator, $empty) {
            return $ViewFactory->renderEach($view, $data, $iterator, $empty);
        });
    }
    /**
     * 加载模板输出
     * 
     * @param  string  $view
     * @param  \Illuminate\Contracts\Support\Arrayable|array  $data
     * @param  array  $mergeData
     * @return \Illuminate\Http\Response
     *
     * @throws \InvalidArgumentException
     */
    protected function fetch(string $view, $data = [], array $mergeData = [])
    {
        $data = array_merge($this->__data, $this->parseData($data));
        return $this->getContent(function ($ViewFactory) use($view, $data, $mergeData) {
            if (!$ViewFactory->exists($view)) {
                return $ViewFactory->file($view, $data, $mergeData)->render();
            }
            return $ViewFactory->make($view, $data, $mergeData)->render();
        });
    }
    /**
     * 渲染内容输出
     * 
     * @param  string $contents
     * @param  \Illuminate\Contracts\Support\Arrayable|array  $data
     * @param  array  $mergeData
     * @return \Illuminate\Http\Response
     *
     * @throws \InvalidArgumentException
     */
    protected function display(string $contents = '', $data = [], array $mergeData = [])
    {
        $data = array_merge($this->__data, $this->parseData($data));
        return $this->getContent(function ($ViewFactory) use($contents, $data, $mergeData) {
            $parts = \array_slice(\str_split($hash = \sha1(\md5($contents)), 2), 0, 2);
            $path = $this->app['cache']->getDirectory() . \DIRECTORY_SEPARATOR . \implode(\DIRECTORY_SEPARATOR, $parts) . \DIRECTORY_SEPARATOR . $hash;
            if (!$this->app['cache']->getFilesystem()->exists(\dirname($path))) {
                $this->app['cache']->getFilesystem()->makeDirectory(\dirname($path), 0777, true, true);
            }
            $result = $this->app['cache']->getFilesystem()->put($path, $contents, true);
            $content = '';
            if ($result !== false && $result > 0) {
                $content = $ViewFactory->file($path, $data, $mergeData)->render();
            }
            if ($this->app['cache']->getFilesystem()->exists($path)) {
                $this->app['cache']->getFilesystem()->delete($path);
            }
            return $content;
        });
    }
    /**
     * 获取模板引擎渲染内容
     *
     * @param \Closure $callback
     * @return \Illuminate\Http\Response
     *
     * @throws \Exception
     */
    protected function getContent(\Closure $callback)
    {
        // 渲染输出
        try {
            $content = $callback($this->view);
            foreach ($this->filters as $filter) {
                if ($filter) {
                    $content = $filter($content);
                }
            }
        } catch (\Exception $e) {
            throw $e;
        }
        if ($this->app['config']->get('view.strip_space')) {
            /* 去除html空格与换行 */
            $find = ['~>\\s+<~', '~>(\\s+\\n|\\r)~'];
            $replace = ['><', '>'];
            $content = preg_replace($find, $replace, $content);
        }
        //尝试写入静态缓存
        if (!$this->app['config']->get('app.debug') && $this->app['request']->isMethod('GET')) {
            $this->writeHtmlCache($content);
        }
        return new Response($content);
    }
    /**
     * 模板变量赋值（当前模板）
     * 
     * @param  string|array $name  模板变量
     * @param  mixed        $value 变量值
     * @return $this
     */
    protected function assign($name, $value = null)
    {
        $data = $this->parseData($name);
        $keys = is_array($data) ? $data : [$name => $value];
        foreach ($keys as $key => $value) {
            $this->__data[$key] = $value;
        }
        return $this;
    }
    /**
     * 模板变量赋值（共享数据）
     *
     * @param  string|array $name  模板变量
     * @param  mixed        $value 变量值
     * @return $this
     */
    protected function share($name, $value = null)
    {
        $this->view->share($name, $value);
        return $this;
    }
    /**
     * 将给定数据解析为原始数组
     *
     * @param  mixed  $data
     * @return array
     */
    protected function parseData($data)
    {
        return $data instanceof Arrayable ? $data->toArray() : $data;
    }
    /**
     * 确定容器是否具有视图内容过滤
     *
     * @param  string  $method
     * @return bool
     */
    protected function hasFilter($method)
    {
        return isset($this->filters[$method]);
    }
    /**
     * 视图内容过滤
     * 
     * @param  array|string  $method
     * @param  \Closure  $callback
     * @return $this
     */
    protected function filter($method, \Closure $callback = null)
    {
        $this->filters[$this->parseFilter($method)] = $callback;
        return $this;
    }
    /**
     * 获取要绑定的方法class@method
     *
     * @param  array|string  $method
     * @return string
     */
    protected function parseFilter($method)
    {
        if (is_array($method)) {
            return $method[0] . '@' . $method[1];
        }
        return $method;
    }
    /**
     * 获取引擎解析器实例
     * 
     * @return \Illuminate\View\Engines\EngineResolver
     */
    protected function engine()
    {
        return $this->view->getEngineResolver();
    }
    /**
     * 为给定路径获取适当的视图引擎
     * 
     * @param  string  $path
     * @return \Illuminate\Contracts\View\Engine
     *
     * @throws \InvalidArgumentException
     */
    protected function getEngineFromPath($path)
    {
        return $this->view->getEngineFromPath($path);
    }
}