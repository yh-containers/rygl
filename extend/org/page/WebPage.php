<?php
namespace org\page;

class WebPage extends \think\paginator\driver\Bootstrap
{
    /**
     * 渲染分页html
     * @return mixed
     */
    public function render()
    {
        if ($this->hasPages()) {
            if ($this->simple) {
                return sprintf(
                    '<div class="layui-box layui-laypage layui-laypage-default">%s %s</div>',
                    $this->getPreviousButton(),
                    $this->getNextButton()
                );
            } else {
                return sprintf(
                    '<div class="layui-box layui-laypage layui-laypage-default">%s %s %s</div>',
                    $this->getPreviousButton('上一页'),
                    $this->getLinks(),
                    $this->getNextButton('下一页')
                );
            }
        }
    }


    /**
     * 生成一个可点击的按钮
     *
     * @param  string $url
     * @param  int    $page
     * @return string
     */
    protected function getAvailablePageWrapper($url, $page)
    {
        return '<a href="' . htmlentities($url) . '">' . $page . '</a>';
    }

    /**
     * 生成一个禁用的按钮
     *
     * @param  string $text
     * @return string
     */
    protected function getDisabledTextWrapper($text)
    {
        return '<a href="javascript:;" class="layui-laypage-prev layui-disabled" data-page="0">'.$text.'</a>';//'<span>' . $text . '</span>';
    }

    /**
     * 生成一个激活的按钮
     *
     * @param  string $text
     * @return string
     */
    protected function getActivePageWrapper($text)
    {
        return '<span class="layui-laypage-curr"><em class="layui-laypage-em"></em><em>'.$text.'</em></span>';//'<span>' . $text . '</span>';
    }

}