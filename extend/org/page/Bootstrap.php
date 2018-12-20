<?php
namespace org\page;

class Bootstrap extends \think\paginator\driver\Bootstrap
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
                    '<ul class="pager">%s %s</ul>',
                    $this->getPreviousButton(),
                    $this->getNextButton()
                );
            } else {
                return sprintf(
                    '<div class="dataTables_wrapper"><div class="dataTables_paginate paging_full_numbers">%s %s %s</div></div>',
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
        return '<a class="paginate_button" href="' . htmlentities($url) . '">' . $page . '</a>';
    }

    /**
     * 生成一个禁用的按钮
     *
     * @param  string $text
     * @return string
     */
    protected function getDisabledTextWrapper($text)
    {
        return '<span class="paginate_button">' . $text . '</span>';
    }

    /**
     * 生成一个激活的按钮
     *
     * @param  string $text
     * @return string
     */
    protected function getActivePageWrapper($text)
    {
        return '<span class="paginate_button current">' . $text . '</span>';
    }

}

/*
 * <div class="dataTables_paginate paging_full_numbers" id="DataTables_Table_0_paginate"><a class="paginate_button first disabled" aria-controls="DataTables_Table_0" data-dt-idx="0" tabindex="0" id="DataTables_Table_0_first">第一页</a><a class="paginate_button previous disabled" aria-controls="DataTables_Table_0" data-dt-idx="1" tabindex="0" id="DataTables_Table_0_previous">上一页</a><span><a class="paginate_button current" aria-controls="DataTables_Table_0" data-dt-idx="2" tabindex="0">1</a><a class="paginate_button " aria-controls="DataTables_Table_0" data-dt-idx="3" tabindex="0">2</a></span><a class="paginate_button next" aria-controls="DataTables_Table_0" data-dt-idx="4" tabindex="0" id="DataTables_Table_0_next">下一页</a><a class="paginate_button last" aria-controls="DataTables_Table_0" data-dt-idx="5" tabindex="0" id="DataTables_Table_0_last">最后一页</a></div>
 * */

