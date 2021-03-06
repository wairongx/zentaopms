<?php
/**
 * The whitelist view of personnel module of ZenTaoPMS.
 *
 * @copyright   Copyright 2009-2015 青岛易软天创网络科技有限公司(QingDao Nature Easy Soft Network Technology Co,LTD, www.cnezsoft.com)
 * @license     ZPL (http://zpl.pub/page/zplv12.html)
 * @author      Chunsheng Wang <chunsheng@cnezsoft.com>
 * @package     personnel
 * @version     $Id
 * @link        http://www.zentao.net
 */
?>
<?php include '../../common/view/header.html.php';?>
<div id="mainMenu" class="clearfix">
  <div class="btn-toolbar pull-left">
    <?php if($module == 'program') echo html::a($goback, $lang->goback, '', 'class="btn btn-secondary"');?>
    <?php echo html::a($this->createLink($module, 'whitelist', "objectID=$objectID"), '<span class="text">' . $lang->personnel->whitelist . '</span>', '', 'class="btn btn-link btn-active-text"');?>
  </div>
  <div class="btn-toolbar pull-right">
    <?php $moduleMethod = $module == 'program' ? 'PRJAddWhitelist' : 'addWhitelist';?>
    <?php common::printLink($module, $moduleMethod, "objectID=$objectID", "<i class='icon icon-plus'></i>" . $lang->personnel->addWhitelist, '', "class='btn btn-primary'");?>
  </div>
</div>
<div id='mainContent' class='main-row fade'>
  <div class='main-col'>
    <?php if(!empty($whitelist)):?>
    <form class='main-table table-user' data-ride='table' action='' method='post' id='userListForm'>
      <table class='table has-sort-head' id='userList'>
        <thead>
        <tr>
          <th class='c-id'>
            <?php echo $lang->idAB;?>
          </th>
          <th><?php echo $lang->user->realname;?></th>
          <th class="w-120px"><?php echo $lang->user->role;?></th>
          <th class="w-120px"><?php echo $lang->user->phone;?></th>
          <th class="w-120px"><?php echo $lang->user->qq;?></th>
          <th class="w-120px"><?php echo $lang->user->weixin;?></th>
          <th class="w-200px"><?php echo $lang->user->email;?></th>
          <th class='c-actions w-100px'><?php echo $lang->actions;?></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach($whitelist as $user):?>
        <tr>
          <td class='c-id'>
            <?php printf('%03d', $user->id);?>
          </td>
          <td><?php echo $user->realname;?></td>
          <td><?php echo zget($lang->user->roleList, $user->role);?></td>
          <td title="<?php echo $user->phone;?>"><?php echo $user->phone;?></td>
          <td title="<?php echo $user->qq;?>"><?php echo $user->qq;?></td>
          <td title="<?php echo $user->weixin;?>"><?php echo $user->weixin;?></td>
          <td title="<?php echo $user->email;?>"><?php echo $user->email;?></td>
          <td class='c-actions'>
            <?php
            $deleteClass = common::hasPriv($module, 'unbindWhielist') ? 'btn' : 'btn disabled';
            echo html::a($this->createLink($module, 'unbindWhielist', "id=$user->id&confirm=no"), '<i class="icon-unlink"></i>', 'hiddenwin', "title='{$lang->delete}' class='{$deleteClass}'");
            ?>
          </td>
        </tr>
        <?php endforeach;?>
        </tbody>
      </table>
      <?php if($whitelist):?>
      <div class='table-footer'>
        <?php $pager->show('right', 'pagerjs');?>
      </div>
      <?php endif;?>
    </form>
    <?php else:?>
    <div class='table-empty-tip'><?php echo $lang->noData;?></div>
    <?php endif;?>
  </div>
</div>
<?php include '../../common/view/footer.html.php';?>
