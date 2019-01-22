<?php namespace helper;
use system\Dispatcher;
error_reporting(~E_NOTICE);

class Html extends Dispatcher {
    
    private $sort_type;
    private $auto_increment = 1;
    public $body = '';
    public $header;
    
    /**
     * return table html
     * 
     * @param array $data
     * @return string
     */
    protected function table(array $data) {
        if (isset($data['auto_increment'])) {
            $this->auto_increment = $data['auto_increment'];
        }
        //thead
        $thead = '<tr>';
        $params = $this->request->getParams;
        
        foreach ($data['columns'] as $key => $row) {
            $class = isset($row['class']) ? "class='".$row['class']."'" : '';
            $label = isset($row['label']) ? $row['label'] : ucfirst($row['field']);
            
            //是否排序
            $sort = empty($row['sort']) ? $row['field'] : $row['sort'];
            
            $flag = isset($params['sort']) && substr($params['sort'], 1) == $sort ? '' : '-';
            $sort_href = Url::setParam($this->request->getUrl, 'sort', $flag.$sort);
            $thead .= $sort == '@' ? "<th $class>$label</th>" : "<th $class><a href='{$sort_href}'>$label</a></th>";
        }
        $thead .= '</tr>';
        if (!empty($params['sort'])) {
            $this->sort_type = ('-' == ($a = substr($params['sort'], 0, 1))) ? substr($params['sort'], 1) : $params['sort'];
            uasort($data['data'], [$this,'-' == $a ? 'descsort' : 'ascsort']);
        }
        //tbody
	$tbody = '';
	if(empty($data['data'])) {$data['data'] = [];}
        foreach ($data['data'] as $key => $value) {
            $tbody .= '<tr>';
	    foreach ($data['columns'] as $v) {
		$tdv = '';    
		if(!empty($v['td'])) {   
		    foreach($v['td'] as $key => $dv) {
		        $tdv .= " $key='$dv'";
		    }
		}
		$td = "<td{$tdv}>";    
                if ($v['field'] == '@') {
                    $tbody .= $td.$this->getValue($v['value'],$value).'</td>';
                } else {
                    if (isset($v['value'])) {
                        $tbody .= $td.$this->getValue($v['value'],$value[$v['field']]).'</td>';
                    } else {
                        $tbody .= $td.$value[$v['field']].'</td>';
                    }
                }
            }
            $tbody .= '</tr>';
        }
        $header = isset($data['count']) ? "<div class='pageheader'>Total count : ".$data['count']."</div>" : '';
        
        $table = "<table class='table table-bordered'><thead>$thead</thead></tbody>$tbody</tbody></table>";
        
        $page = '';
        if (!empty($data['data'][0]) && isset($data['count'])) {
            $url = $this->request->getUrl;
            if (isset($this->request->getParams['page'])) {
                $currentPage = $this->request->getParams['page'];
		$url = str_replace('page='.$currentPage, '', $url);
		$url = trim($url,'&');
		$url = trim($url,'?');
            } else {
                $currentPage = 1;
            }
            $option = [
                'limit' => $data['limit'],
                'totalCount' => $data['count'],
                'firstLabel' => $data['page']['firstLabel'],
                'lastLabel' => $data['page']['lastLabel'],
                'currentPage' => $currentPage,
                'url' => $url
            ];
            $page = $this->page($option);
        }
        return $header.$table.$page;        
    }
    
    
    
    /**
     * return page html
     * 
     * @param array $option
     * @return string
     */
    protected function page($option) {
        $page_num = (int)ceil($option['totalCount'] / $option['limit']);
        if ($page_num < 2) {
            return '';
	}
        $tmp = explode('?', $option['url']);
	$sp = empty($tmp[1])?'?':'&';
        
        if ($option['currentPage'] == 1) {
            $first = "<li class='first disabled'><span>".$option['firstLabel']."</span></li><li class='prev disabled'><span>«</span></li>";
        } else {
            $url = $option['url'].$sp.'page=1';
            $prev = $option['url'].$sp.'page='.($option['currentPage']-1); 
            $first = "<li class='first'><a href='".$url."'>".$option['firstLabel']."</a></li><li class='prev'><a href='".$prev."'>«</a></li>";
        }
        if ($option['currentPage'] == $page_num) {
            $last = "<li class='next disabled'><span>»</span></li><li class='last disabled'><span>".$option['lastLabel']."</span></li>";
	} else {
	    $url = $option['url'].$sp."page=$page_num";
	    $next_num = $option['currentPage'] + 1;
	    $next = $option['url'].$sp."page=$page_num";
            $last = "<li class='next'><a href='".$next."'>»</a></li><li class='last'><a href='".$url."'>".$option['lastLabel']."</a></li>";
        }
        //显示10个数字
        $num = '';
        $m = $option['currentPage']-5;
        $start = $m > 1 ? $m : 1;
        
        $n = $start+10;
        
        $end = $n < $page_num ? $n : ($page_num+1);
        
        if ($m > 1 && $n >= $page_num) {
            $start = $page_num-10+1;
		}
        $start = $start < 1 ? 1 : $start;
        $url1 = empty($tmp[1]) ? rtrim($option['url'],'?').'?' : rtrim($option['url'],'&').'&';
        for ($i = $start ;$i < $end; $i++) {
            $class_li = '';
            $url = $url1."page=$i";
            if ($option['currentPage'] == $i) {
                $class_li = "class='active'";
            }
            $num .= "<li $class_li><a href='".$url."'>$i</a></li>";
		}
        return "<ul class='pagination'>$first$num$last</ul>";
    }
    
    
    /**
     * return navigation html
     * 
     * @param array $data
     * @return string
     */
    protected function navigation(array $data) {
        $login = isset($data['showMenuOnlyLogin']) && $data['showMenuOnlyLogin'] ? ($this->request->getUser->isLogin ? true : false) : true;
        
        //LOGO
        $logo_html = '';
        if (!empty($data['logo']['src'])) {
            $logo_html = empty($data['logo']['href']) ? "<div class='nav-logo'><img src='".$data['logo']['src']."'></div>" : "<div class='nav-logo'><a href='".$data['logo']['href']."'><img src='".$data['logo']['src']."'></a></div>";
        }
        
        //TITLE
        $title_html = '';
        if (isset($data['title'])) {
            if (isset($data['title']['value'])) {
                $title_html = "<div class='nav-title'>".$this->getValue($data['title']['value'])."</div>";
                
            } elseif (isset($data['title']['text'])) {
                $title_html = "<div class='nav-title'>".$data['title']['text']."</div>";
            }
        }
        
        //LEFT MENU
        $left_html = '';
        if ($login && isset($data['left']['menus'])) {
            $left_html = "<div class='nav-left ".(empty($data['left']['class']) ? '' : $data['left']['class'])."'><ul>".$this->getMenu($data['left']['menus'])."</ul></div>";
        }
        
        
        //RIGHT MENU
        $right_html = '';
        
        if ($login && isset($data['right']['menus'])) {
            $right_html = "<div class='nav-right ".(empty($data['right']['class']) ? '' : $data['right']['class'])."'><ul>".$this->getMenu($data['right']['menus'])."</ul></div>";
        }
        
        return "<div class='nav'>$logo_html$title_html$left_html$right_html</div><div id='modal-container'></div><div class='alert alert-success'>Success</div><div class='alert alert-failed'>Failed</div>";
    }
    
    
    /**
     * set navigation menu
     * 
     * @param array $data
     * @return string
     */
    protected function getMenu($data) {
        $ctl = substr($this->request->getController, 0,-10);
        //
        $li = '';
        foreach ($data as $menu) {
            $class = isset($menu['class']) ? (is_object($menu['class']) ? $this->getValue($menu['class']) : $menu['class']) : '';

            !is_object($menu['text']) or $menu['text'] = $this->getValue($menu['text']);
            
            if (isset($menu['active'])) {
                $active = (is_object($menu['active'])) ? $this->getValue($menu['active']) : $menu['active'];
            } else {
                
                $active = $ctl == $menu['text'] ? true : false;
            }
            $aclass = $active === true ? 'active' : '';
            
            $li .= "<li class='nav-menu-parent nav-title ".$class."'>";
            if (isset($menu['submenus'])) {
                $subli = '';
                foreach ($menu['submenus'] as $k => $m) {
                    $separater = $k == 0 ? '' : "<li role='separator' class='divider'></li>";
                     $subli .= $separater."<li><a href='".$m['href']."'> ".$m['text']."</a></li>";
                }
                $li .= "<a href='javascript:void(0)' class='".$aclass."'>".$menu['text']." <span class='caret'></span></a><ul class='nav-menu-child'>$subli</ul>";
                
            } else {
                if (isset($menu['value'])) {
                    $li .= $this->getValue($menu['value']);
                
                } elseif (isset($menu['href']) && isset($menu['text'])) {
                    $li .= "<a href='".$menu['href']."' class='".$aclass."'>".$menu['text']."</a>";
                }        
            }
            $li .= "</li>";
        }
        return $li;
    }
    
    /**
     * auto increment id
     * 
     * @return number
     */
    protected function autoIncrement() {
        return $this->auto_increment++;
    }   
    /**
     * sort by array field acs
     * 
     * @param array $a
     * @param array $b
     * @return number
     */
    private function ascsort($a,$b) {
        if ($a[$this->sort_type]==$b[$this->sort_type]) return 0;
        return ($a[$this->sort_type] < $b[$this->sort_type]) ? -1 : 1;
    }
    
    /**
     * sort by array field desc
     * 
     * @param array $a
     * @param array $b
     * @return number
     */
    private function descsort($a,$b) {
        if ($a[$this->sort_type]==$b[$this->sort_type]) return 0;
        return ($a[$this->sort_type] < $b[$this->sort_type]) ? 1 : -1;
    }
    
    
    /**
     * 
     * @param unknown $value
     * @param \Closure $call
     */
    private function getValue(\Closure $call, $value = null) {
        return $call->__invoke($value);
    }
    protected function modal($data) {
	$this->header = [
	    'id' => $data['id'],	
	    'action' => isset($data['url']) ? $data['url'] : '',
	    'title' => isset($data['title']) ? $data['title'] : 'Modal Title',
	    'method' => isset($data['method']) ? $data['method'] : 'post',
	];
	$this->body = '';
	return $this;
    }
    public function text($data) {
    	$this->body .= "<div class='form-group center'><font color='{$data['color']}'>{$data['text']}</font></div>";
	return $this;
    }
    public function footer($data = []) {
	if (empty($data)) {    
	    $html = "<button type='button' class='btn btn-default' data-dismiss='modal'>取消</button><button type='submit' class='btn btn-primary'>确认</button>";
	} else {
	    $html = '';    
	    foreach($data['btns'] as $btn) {
	        $class = isset($btn['class']) ? $btn['class'] : 'default';
	        if('close' == $btn['type'])	{	
	   	   $html .= "<button type='button' class='btn btn-$class' data-dismiss='modal'>{$btn['label']}</button>";
	        } else {	    
	    	    $html .= "<button type='{$btn['type']}' class='btn btn-$class'>{$btn['label']}</button>";
	        }
	    }    
	}
$modal=<<<EOT
<form class="form-horizontal" role="form" method="{$this->header['method']}" action="{$this->header['action']}">
<div class="modal fade" id="{$this->header['id']}" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
        <h4 class="modal-title">{$this->header['title']}</h4>
      </div>
      <div class="modal-body">{$this->body}</div>
      <div class="modal-footer">$html</div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
</form>
EOT;
	return $modal;
    }
    public function input($data) {
	$label = $data['label'];
	$name = $data['name'];
	$required = isset($data['required']) && true == $data['required'] ? 'required' : '';
	$type = empty($data['type']) ? 'text' : $data['type'];
$this->body .= <<<EOT
<div class="form-group">
    <label class="col-sm-2 control-label" for="exampleInputEmail2">$label</label>
    <div class="col-sm-9">
      <input type="$type" name="$name" class="form-control" id="exampleInputEmail2" placeholder="$label" $required>
    </div>
  </div>
EOT;
        return $this;
    }

    public function textarea() {}
    public function radio($data) {
	$html = '';    
	$name = $data['name'];
	$plabel = $data['label'];
	foreach($data['children'] as $iterm) {
	    $label = $iterm['label'];
	    $value = $iterm['value'];
$html .= <<<EOT
<label class="radio-inline">
  <input type="radio" name="$name" value="$value"> $label
</label>
EOT;
	}
	$this->body .= "<div class='form-group'><label class='col-sm-2 control-label'>$plabel</label><div class='col-sm-9'>$html</div></div>";
	return $this;
    }
    public function checkbox() {}
}
