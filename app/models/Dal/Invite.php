<?php

require_once 'Dal/Abstract.php';

class Dal_Invite extends Dal_Abstract
{
    /**
     * app invite table name
     *
     * @var string
     */
    protected $table_invite = 'mixi_app_invite';

    protected static $_instance;

    public static function getDefaultInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function addInvite($app_id, $actor, $target, $time = null)
    {
        if (!$time) {
            $time = time();
        }

        $sql = "INSERT INTO $this->table_invite (app_id, actor, target, time) VALUES (:app_id, :actor, :target, $time)"
             . "  ON DUPLICATE KEY UPDATE time = $time";

        $params = array(
            'app_id' => $app_id,
            'actor' => $actor,
            'target' => $target
        );

        return $this->_wdb->query($sql, $params);
    }

    public function getInvite($app_id, $target)
    {
        $sql = "SELECT actor FROM $this->table_invite WHERE app_id=:app_id AND target=:target AND `process`='waiting'";

        $rows = $this->_rdb->fetchAll($sql, array('app_id' => $app_id, 'target' => $target));

        return $rows;
    }

    public function updateInvite($app_id, $target)
    {
        $time = time();

        $sql = "UPDATE $this->table_invite SET `process`='finished', process_time=$time WHERE app_id=:app_id AND target=:target AND `process`='waiting'";

        return $this->_wdb->query($sql, array('app_id' => $app_id, 'target' => $target));
    }

    public function updateInviteOne($app_id, $target, $actor)
    {
        $time = time();

        $sql = "UPDATE $this->table_invite SET `process`='finished', process_time=$time WHERE app_id=:app_id AND target=:target AND actor=:actor AND `process`='waiting'";

        return $this->_wdb->query($sql, array('app_id' => $app_id, 'target' => $target, 'actor' => $actor));
    }

    public function deleteInvite($app_id, $target)
    {
        $sql = "DELETE FROM $this->table_invite WHERE app_id=:app_id AND target=:target";

        return $this->_wdb->query($sql, array('app_id' => $app_id, 'target' => $target));
    }



}