<?php
/**
 * The control file of dashboard module of ZenTaoPMS.
 *
 * @copyright   Copyright 2009-2015 青岛易软天创网络科技有限公司(QingDao Nature Easy Soft Network Technology Co,LTD, www.cnezsoft.com)
 * @license     ZPL (http://zpl.pub/page/zplv12.html)
 * @author      Chunsheng Wang <chunsheng@cnezsoft.com>
 * @package     dashboard
 * @version     $Id: control.php 5020 2013-07-05 02:03:26Z wyd621@gmail.com $
 * @link        http://www.zentao.net
 */
class my extends control
{
    /**
     * Construct function.
     *
     * @access public
     * @return void
     */
    public function __construct($module = '', $method = '')
    {
        parent::__construct($module, $method);
        $this->loadModel('user');
        $this->loadModel('dept');
        $this->my->setMenu();
    }

    /**
     * Index page, goto todo.
     *
     * @access public
     * @return void
     */
    public function index()
    {
        $this->view->title = $this->lang->my->common;
        $this->display();
    }

    /**
     * Get score list
     *
     * @param int $recTotal
     * @param int $recPerPage
     * @param int $pageID
     *
     * @access public
     * @return mixed
     */
    public function score($recTotal = 0, $recPerPage = 20, $pageID = 1)
    {
        $this->app->loadClass('pager', $static = true);
        $pager  = new pager($recTotal, $recPerPage, $pageID);
        $scores = $this->loadModel('score')->getListByAccount($this->app->user->account, $pager);

        $this->view->title      = $this->lang->score->common;
        $this->view->user       = $this->loadModel('user')->getById($this->app->user->account);
        $this->view->pager      = $pager;
        $this->view->scores     = $scores;
        $this->view->position[] = $this->lang->score->record;

        $this->display();
    }

    public function calendar()
    {
        $this->locate($this->createLink('my', 'todo'));
    }

    /**
     * My todos.
     *
     * @param  string $type
     * @param  string $account
     * @param  string $status
     * @param  int    $recTotal
     * @param  int    $recPerPage
     * @param  int    $pageID
     * @access public
     * @return void
     */
    public function todo($type = 'all', $account = '', $status = 'all', $orderBy = "date_desc,status,begin", $recTotal = 0, $recPerPage = 20, $pageID = 1)
    {
        /* Save session. */
        $uri = $this->app->getURI(true);
        if($this->app->viewType != 'json')
        {
            $this->session->set('todoList', $uri);
            $this->session->set('bugList',  $uri);
            $this->session->set('taskList', $uri);
        }

        /* Load pager. */
        $this->app->loadClass('pager', $static = true);
        if($this->app->getViewType() == 'mhtml') $recPerPage = 10;
        $pager = pager::init($recTotal, $recPerPage, $pageID);

        /* The title and position. */
        $this->view->title      = $this->lang->my->common . $this->lang->colon . $this->lang->my->todo;
        $this->view->position[] = $this->lang->my->todo;

        /* Append id for secend sort. */
        $sort = $this->loadModel('common')->appendOrder($orderBy);

        /* Assign. */
        $this->view->todos        = $this->loadModel('todo')->getList($type, $account, $status, 0, $pager, $sort);
        $this->view->date         = (int)$type == 0 ? date(DT_DATE1) : date(DT_DATE1, strtotime($type));
        $this->view->type         = $type;
        $this->view->recTotal     = $recTotal;
        $this->view->recPerPage   = $recPerPage;
        $this->view->pageID       = $pageID;
        $this->view->status       = $status;
        $this->view->account      = $this->app->user->account;
        $this->view->orderBy      = $orderBy == 'date_desc,status,begin,id_desc' ? '' : $orderBy;
        $this->view->pager        = $pager;
        $this->view->times        = date::buildTimeList($this->config->todo->times->begin, $this->config->todo->times->end, $this->config->todo->times->delta);
        $this->view->time         = date::now();
        $this->view->importFuture = ($type != 'today');

        $this->display();
    }

    /**
     * My requirement. 

     * @param  string $type
     * @param  int    $recTotal
     * @param  int    $recPerPage
     * @param  int    $pageID
     * @access public
     * @return void
     */
    public function requirement($type = 'assignedTo', $orderBy = 'id_desc', $recTotal = 0, $recPerPage = 20, $pageID = 1)
    {
        /* Save session. */
        if($this->app->viewType != 'json') $this->session->set('storyList', $this->app->getURI(true));

        /* Load pager. */
        $this->app->loadClass('pager', $static = true);
        if($this->app->getViewType() == 'mhtml') $recPerPage = 10;
        $pager = pager::init($recTotal, $recPerPage, $pageID);

        /* Append id for secend sort. */
        $sort = $this->loadModel('common')->appendOrder($orderBy);

        /* Assign. */
        $this->view->title      = $this->lang->my->common . $this->lang->colon . $this->lang->my->story;
        $this->view->position[] = $this->lang->my->story;
        $this->view->stories    = $this->loadModel('story')->getUserStories($this->app->user->account, $type, $sort, $pager, 'requirement');
        $this->view->users      = $this->user->getPairs('noletter');
        $this->view->type       = $type;
        $this->view->programs   = $this->loadModel('program')->getPRJPairs();
        $this->view->recTotal   = $recTotal;
        $this->view->recPerPage = $recPerPage;
        $this->view->pageID     = $pageID;
        $this->view->orderBy    = $orderBy;
        $this->view->pager      = $pager;

        $this->display();
    }

    /**
     * My stories

     * @param  string $type
     * @param  int    $recTotal
     * @param  int    $recPerPage
     * @param  int    $pageID
     * @access public
     * @return void
     */
    public function story($type = 'assignedTo', $orderBy = 'id_desc', $recTotal = 0, $recPerPage = 20, $pageID = 1)
    {
        /* Save session. */
        if($this->app->viewType != 'json') $this->session->set('storyList', $this->app->getURI(true));

        /* Load pager. */
        $this->app->loadClass('pager', $static = true);
        if($this->app->getViewType() == 'mhtml') $recPerPage = 10;
        $pager = pager::init($recTotal, $recPerPage, $pageID);

        /* Append id for secend sort. */
        $sort = $this->loadModel('common')->appendOrder($orderBy);

        /* Assign. */
        $this->view->title      = $this->lang->my->common . $this->lang->colon . $this->lang->my->story;
        $this->view->position[] = $this->lang->my->story;
        $this->view->stories    = $this->loadModel('story')->getUserStories($this->app->user->account, $type, $sort, $pager);
        $this->view->users      = $this->user->getPairs('noletter');
        $this->view->programs   = $this->loadModel('program')->getPRJPairs();
        $this->view->type       = $type;
        $this->view->recTotal   = $recTotal;
        $this->view->recPerPage = $recPerPage;
        $this->view->pageID     = $pageID;
        $this->view->orderBy    = $orderBy;
        $this->view->pager      = $pager;

        $this->display();
    }

    /**
     * My tasks
     *
     * @param  string $type
     * @param  int    $recTotal
     * @param  int    $recPerPage
     * @param  int    $pageID
     * @access public
     * @return void
     */
    public function task($type = 'assignedTo', $orderBy = 'id_desc', $recTotal = 0, $recPerPage = 20, $pageID = 1)
    {
        /* Save session. */
        if($this->app->viewType != 'json')
        {
            $this->session->set('taskList',  $this->app->getURI(true));
            $this->session->set('storyList', $this->app->getURI(true));
        }

        /* Load pager. */
        $this->app->loadClass('pager', $static = true);
        if($this->app->getViewType() == 'mhtml') $recPerPage = 10;
        $pager = pager::init($recTotal, $recPerPage, $pageID);

        /* append id for secend sort. */
        $sort = $this->loadModel('common')->appendOrder($orderBy);

        /* Get the story language configuration. */
        $this->app->loadLang('story');

        /* Assign. */
        $this->view->title      = $this->lang->my->common . $this->lang->colon . $this->lang->my->task;
        $this->view->position[] = $this->lang->my->task;
        $this->view->tabID      = 'task';
        $this->view->tasks      = $this->loadModel('task')->getUserTasks($this->app->user->account, $type, 0, $pager, $sort);
        $this->view->type       = $type;
        $this->view->recTotal   = $recTotal;
        $this->view->recPerPage = $recPerPage;
        $this->view->pageID     = $pageID;
        $this->view->orderBy    = $orderBy;
        $this->view->programs   = $this->loadModel('program')->getPRJPairs();
        $this->view->users      = $this->loadModel('user')->getPairs('noletter');
        $this->view->pager      = $pager;

        if($this->app->viewType == 'json') $this->view->tasks = array_values($this->view->tasks);
        $this->display();
    }

    /**
     * My bugs.
     *
     * @param  string $type
     * @param  string $orderBy
     * @param  int    $recTotal
     * @param  int    $recPerPage
     * @param  int    $pageID
     * @access public
     * @return void
     */
    public function bug($type = 'assignedTo', $orderBy = 'id_desc', $recTotal = 0, $recPerPage = 20, $pageID = 1)
    {
        /* Save session. load Lang. */
        if($this->app->viewType != 'json') $this->session->set('bugList', $this->app->getURI(true));
        $this->app->loadLang('bug');

        /* Load pager. */
        $this->app->loadClass('pager', $static = true);
        if($this->app->getViewType() == 'mhtml') $recPerPage = 10;
        $pager = pager::init($recTotal, $recPerPage, $pageID);

        /* Append id for secend sort. */
        $sort = $this->loadModel('common')->appendOrder($orderBy);
        $bugs = $this->loadModel('bug')->getUserBugs($this->app->user->account, $type, $sort, 0, $pager);
        $this->loadModel('common')->saveQueryCondition($this->dao->get(), 'bug');

        /* assign. */
        $this->view->title       = $this->lang->my->common . $this->lang->colon . $this->lang->my->bug;
        $this->view->position[]  = $this->lang->my->bug;
        $this->view->bugs        = $bugs;
        $this->view->users       = $this->user->getPairs('noletter');
        $this->view->memberPairs = $this->user->getPairs('noletter|nodeleted');
        $this->view->tabID       = 'bug';
        $this->view->type        = $type;
        $this->view->recTotal    = $recTotal;
        $this->view->recPerPage  = $recPerPage;
        $this->view->pageID      = $pageID;
        $this->view->orderBy     = $orderBy;
        $this->view->pager       = $pager;

        $this->display();
    }

    /**
     * My test task.
     *
     * @access public
     * @return void
     */
    public function testtask($type = 'wait', $orderBy = 'id_desc', $recTotal = 0, $recPerPage = 20, $pageID = 1)
    {
        /* Load pager. */
        $this->app->loadClass('pager', $static = true);
        $pager = pager::init($recTotal, $recPerPage, $pageID);

        /* Save session. */
        if($this->app->viewType != 'json') $this->session->set('testtaskList', $this->app->getURI(true));

        $this->app->loadLang('testcase');

        /* Append id for secend sort. */
        $sort = $this->loadModel('common')->appendOrder($orderBy);

        $this->view->title      = $this->lang->my->common . $this->lang->colon . $this->lang->my->testTask;
        $this->view->position[] = $this->lang->my->testTask;
        $this->view->tasks      = $this->loadModel('testtask')->getByUser($this->app->user->account, $pager, $sort, $type);

        $this->view->recTotal   = $recTotal;
        $this->view->recPerPage = $recPerPage;
        $this->view->pageID     = $pageID;
        $this->view->orderBy    = $orderBy;
        $this->view->type       = $type;
        $this->view->pager      = $pager;
        $this->display();

    }

    /**
     * My test case.
     *
     * @param  string $type
     * @param  string $orderBy
     * @param  int    $recTotal
     * @param  int    $recPerPage
     * @param  int    $pageID
     * @access public
     * @return void
     */
    public function testcase($type = 'assigntome', $orderBy = 'id_desc', $recTotal = 0, $recPerPage = 20, $pageID = 1)
    {
        /* Save session, load lang. */
        if($this->app->viewType != 'json') $this->session->set('caseList', $this->app->getURI(true));
        $this->app->loadLang('testcase');
        $this->app->loadLang('testtask');

        /* Load pager. */
        $this->app->loadClass('pager', $static = true);
        $pager = pager::init($recTotal, $recPerPage, $pageID);

        /* Append id for secend sort. */
        $sort = $this->loadModel('common')->appendOrder($orderBy);

        $cases = array();
        if($type == 'assigntome')
        {
            $cases = $this->loadModel('testcase')->getByAssignedTo($this->app->user->account, $sort, $pager, 'skip');
        }
        elseif($type == 'openedbyme')
        {
            $cases = $this->loadModel('testcase')->getByOpenedBy($this->app->user->account, $sort, $pager, 'skip');
        }
        $this->loadModel('common')->saveQueryCondition($this->dao->get(), 'testcase', $type == 'assigntome' ? false : true);

        $cases = $this->testcase->appendData($cases, $type == 'assigntome' ? 'run' : 'case');

        /* Assign. */
        $this->view->title      = $this->lang->my->common . $this->lang->colon . $this->lang->my->testCase;
        $this->view->position[] = $this->lang->my->testCase;
        $this->view->cases      = $cases;
        $this->view->users      = $this->user->getPairs('noletter');
        $this->view->tabID      = 'test';
        $this->view->type       = $type;
        $this->view->summary    = $this->testcase->summary($cases);
        $this->view->recTotal   = $recTotal;
        $this->view->recPerPage = $recPerPage;
        $this->view->pageID     = $pageID;
        $this->view->orderBy    = $orderBy;
        $this->view->pager      = $pager;

        $this->display();
    }

    /**
     * My projects.
     *
     * @access public
     * @return void
     */
    public function project()
    {
        $this->app->loadLang('project');

        $this->view->title      = $this->lang->my->common . $this->lang->colon . $this->lang->my->myProject;
        $this->view->position[] = $this->lang->my->myProject;
        $this->view->tabID      = 'project';
        $this->view->projects   = $this->user->getProjects($this->app->user->account, 'sprint');

        $this->display();
    }

    /**
     * My programs.
     *
     * @param  string  $status
     * @param  string  $orderBy
     * @param  int     $recTotal
     * @param  int     $recPerPage
     * @param  int     $pageID
     * @access public
     * @return void
     */
    public function program($status = 'all', $recTotal = 0, $recPerPage = 15, $pageID = 1)
    {
        $this->loadModel('program');
        $this->app->loadLang('project');

        $this->app->session->set('programList', $this->app->getURI(true));

        /* Set the pager. */
        $this->app->loadClass('pager', $static = true);
        $pager = new pager($recTotal, $recPerPage, $pageID);

        $this->view->title      = $this->lang->my->common . $this->lang->colon . $this->lang->my->myProgram;
        $this->view->position[] = $this->lang->my->myProgram;
        $this->view->users      = $this->loadModel('user')->getPairs('noletter');
        $this->view->projects   = $this->user->getProjects($this->app->user->account, 'project', $pager);
        $this->view->pager      = $pager;
        $this->view->status     = $status;
        $this->display();
    }

    /**
     * Edit profile
     *
     * @access public
     * @return void
     */
    public function editProfile()
    {
        if($this->app->user->account == 'guest') die(js::alert('guest') . js::locate('back'));
        if(!empty($_POST))
        {
            $_POST['account'] = $this->app->user->account;
            $this->user->update($this->app->user->id);
            if(dao::isError()) die(js::error(dao::getError()));
            die(js::locate($this->createLink('my', 'profile'), 'parent'));
        }

        $this->app->loadConfig('user');
        $this->app->loadLang('user');

        $userGroups = $this->loadModel('group')->getByAccount($this->app->user->account);

        $this->view->title      = $this->lang->my->common . $this->lang->colon . $this->lang->my->editProfile;
        $this->view->position[] = $this->lang->my->editProfile;
        $this->view->user       = $this->user->getById($this->app->user->account);
        $this->view->rand       = $this->user->updateSessionRandom();
        $this->view->userGroups = implode(',', array_keys($userGroups));
        $this->view->groups     = $this->dao->select('id, name')->from(TABLE_GROUP)->fetchPairs('id', 'name');

        $this->display();
    }

    /**
     * Change password
     *
     * @access public
     * @return void
     */
    public function changePassword()
    {
        if($this->app->user->account == 'guest') die(js::alert('guest') . js::locate('back'));
        if(!empty($_POST))
        {
            $this->user->updatePassword($this->app->user->id);
            if(dao::isError()) die(js::error(dao::getError()));
            die(js::locate($this->createLink('my', 'profile'), 'parent.parent'));
        }

        $this->view->title      = $this->lang->my->common . $this->lang->colon . $this->lang->my->changePassword;
        $this->view->position[] = $this->lang->my->changePassword;
        $this->view->user       = $this->user->getById($this->app->user->account);
        $this->view->rand       = $this->user->updateSessionRandom();

        $this->display();
    }

    /**
     * Manage contacts.
     *
     * @param  int    $listID
     * @param  string $mode
     * @access public
     * @return void
     */
    public function manageContacts($listID = 0, $mode = '')
    {
        if($_POST)
        {
            $data = fixer::input('post')->get();
            if($data->mode == 'new')
            {
                $listID = $this->user->createContactList($data->newList, $data->users);
                $this->user->setGlobalContacts($listID, isset($data->share));
                if(isonlybody()) die(js::closeModal('parent.parent', '', ' function(){parent.parent.ajaxGetContacts(\'#mailto\')}'));
                die(js::locate(inlink('manageContacts', "listID=$listID"), 'parent'));
            }
            elseif($data->mode == 'edit')
            {
                $this->user->updateContactList($data->listID, $data->listName, $data->users);
                $this->user->setGlobalContacts($data->listID, isset($data->share));
                die(js::locate(inlink('manageContacts', "listID={$data->listID}"), 'parent'));
            }
        }

        $mode  = empty($mode) ? 'edit' : $mode;
        $lists = $this->user->getContactLists($this->app->user->account);

        $globalContacts = isset($this->config->my->global->globalContacts) ? $this->config->my->global->globalContacts : '';
        $globalContacts = !empty($globalContacts) ? explode(',', $globalContacts) : array();

        $myContacts = $this->user->getListByAccount($this->app->user->account);
        $disabled   = $globalContacts;

        if(!empty($myContacts) && !empty($globalContacts))
        {
            foreach($globalContacts as $id)
            {
                if(in_array($id, array_keys($myContacts))) unset($disabled[array_search($id, $disabled)]);
            }
        }

        $listID = $listID ? $listID : key($lists);
        if(!$listID) $mode = 'new';

        /* Create or manage list according to mode. */
        if($mode == 'new')
        {
            $this->view->title      = $this->lang->my->common . $this->lang->colon . $this->lang->user->contacts->createList;
            $this->view->position[] = $this->lang->user->contacts->createList;
        }
        else
        {
            $this->view->title      = $this->lang->my->common . $this->lang->colon . $this->lang->user->contacts->manage;
            $this->view->position[] = $this->lang->user->contacts->manage;
            $this->view->list       = $this->user->getContactListByID($listID);
        }

        $users = $this->user->getPairs('noletter|noempty|noclosed|noclosed', $mode == 'new' ? '' : $this->view->list->userList, $this->config->maxCount);
        if(isset($this->config->user->moreLink)) $this->config->moreLinks['users[]'] = $this->config->user->moreLink;

        $this->view->mode           = $mode;
        $this->view->lists          = $lists;
        $this->view->listID         = $listID;
        $this->view->users          = $users;
        $this->view->disabled       = $disabled;
        $this->view->globalContacts = $globalContacts;
        $this->display();
    }

    /**
     * Delete a contact list.
     *
     * @param  int    $listID
     * @param  string $confirm
     * @access public
     * @return void
     */
    public function deleteContacts($listID, $confirm = 'no')
    {
        if($confirm == 'no')
        {
            die(js::confirm($this->lang->user->contacts->confirmDelete, inlink('deleteContacts', "listID=$listID&confirm=yes")));
        }
        else
        {
            $this->user->deleteContactList($listID);
            die(js::locate(inlink('manageContacts'), 'parent'));
        }
    }

    /**
     * Build contact lists.
     *
     * @access public
     * @return void
     */
    public function buildContactLists()
    {
        $this->view->contactLists = $this->user->getContactLists($this->app->user->account, 'withnote');
        $this->display();
    }

    /**
     * View my profile.
     *
     * @access public
     * @return void
     */
    public function profile()
    {
        if($this->app->user->account == 'guest') die(js::alert('guest') . js::locate('back'));

        $this->app->loadConfig('user');
        $this->app->loadLang('user');
        $user = $this->user->getById($this->app->user->account);

        $this->view->title        = $this->lang->my->common . $this->lang->colon . $this->lang->my->profile;
        $this->view->position[]   = $this->lang->my->profile;
        $this->view->user         = $user;
        $this->view->groups       = $this->loadModel('group')->getByAccount($this->app->user->account);
        $this->view->deptPath     = $this->dept->getParents($user->dept);
        $this->view->personalData = $this->user->getPersonalData();
        $this->display();
    }

    /**
     * My dynamic.
     *
     * @param  string $type
     * @param  string $orderBy
     * @param  int    $recTotal
     * @param  int    $recPerPage
     * @param  int    $pageID
     * @access public
     * @return void
     */
    public function dynamic($type = 'today', $recTotal = 0, $date = '', $direction = 'next')
    {
        /* Save session. */
        $uri = $this->app->getURI(true);
        $this->session->set('productList',     $uri);
        $this->session->set('productPlanList', $uri);
        $this->session->set('releaseList',     $uri);
        $this->session->set('storyList',       $uri);
        $this->session->set('projectList',     $uri);
        $this->session->set('taskList',        $uri);
        $this->session->set('buildList',       $uri);
        $this->session->set('bugList',         $uri);
        $this->session->set('caseList',        $uri);
        $this->session->set('testtaskList',    $uri);

        /* Set the pager. */
        $this->app->loadClass('pager', $static = true);
        $pager = new pager($recTotal, $recPerPage = 50, $pageID = 1);

        /* Append id for secend sort. */
        $orderBy = $direction == 'next' ? 'date_desc' : 'date_asc';
        $sort = $this->loadModel('common')->appendOrder($orderBy);

        /* The header and position. */
        $this->view->title      = $this->lang->my->common . $this->lang->colon . $this->lang->my->dynamic;
        $this->view->position[] = $this->lang->my->dynamic;

        $date    = empty($date) ? '' : date('Y-m-d', $date);
        $actions = $this->loadModel('action')->getDynamic($this->app->user->account, $type, $sort, $pager, 'all', 'all', $date, $direction);

        /* Assign. */
        $this->view->type       = $type;
        $this->view->orderBy    = $orderBy;
        $this->view->pager      = $pager;
        $this->view->dateGroups = $this->action->buildDateGroup($actions, $direction, $type);
        $this->view->direction  = $direction;
        $this->display();
    }

    /**
     * Unbind ranzhi
     *
     * @param  string $confirm
     * @access public
     * @return void
     */
    public function unbind($confirm = 'no')
    {
        $this->loadModel('user');
        if($confirm == 'no')
        {
            die(js::confirm($this->lang->user->confirmUnbind, $this->createLink('my', 'unbind', "confirm=yes")));
        }
        else
        {
            $this->user->unbind($this->app->user->account);
            die(js::locate($this->createLink('my', 'profile'), 'parent'));
        }
    }
}
