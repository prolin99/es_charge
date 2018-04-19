CREATE TABLE `charge_item` (
  `item_id` int(11) NOT NULL AUTO_INCREMENT,
  `item_type` varchar(20) DEFAULT NULL,
  `item` varchar(40) NOT NULL DEFAULT '',
  `authority` varchar(40) NOT NULL DEFAULT '',
  `paid_method` varchar(40) NOT NULL DEFAULT '',
  `announce_note` varchar(40) NOT NULL DEFAULT '',
  `announce_note2` varchar(40) NOT NULL DEFAULT '',
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `comment` varchar(40) DEFAULT NULL,
  `creater` varchar(20) DEFAULT NULL,
  `cooperate` tinyint(4) DEFAULT NULL,
  `bank_date` date DEFAULT NULL,
  `p_rec_num` int(11) NOT NULL DEFAULT '0',
  `p_sum` int(11) NOT NULL DEFAULT '0',
  `c_rec_num` int(11) NOT NULL DEFAULT '0',
  `c_sum` int(11) NOT NULL DEFAULT '0',
  `bank_id` int(11) NOT NULL DEFAULT '1' ,
  PRIMARY KEY (`item_id`)
) ENGINE=MyISAM   ;


CREATE TABLE `charge_detail` (
  `detail_id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) NOT NULL DEFAULT '0',
  `detail_sort` char(3) DEFAULT NULL,
  `detail` varchar(50) NOT NULL DEFAULT '',
  `dollars` varchar(40) NOT NULL DEFAULT '0',
  PRIMARY KEY (`detail_id`)
) ENGINE=MyISAM  ;


CREATE TABLE `charge_record` (
    `record_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `sit_num` int(11) NOT NULL,
    `student_sn` int(11) NOT NULL DEFAULT '0',
    `item_id` int(11) NOT NULL DEFAULT '0',
    `dollars` int(11) NOT NULL DEFAULT '0',
    `in_bank` tinyint(4) NOT NULL DEFAULT '1',
    `class_id` varchar(6)   NOT NULL,
    `cause` int(11) NOT NULL DEFAULT '0',
    `ps` varchar(200) NOT NULL DEFAULT '',
    `rec_name` varchar(20)   NOT NULL DEFAULT '',
    `end_pay` int(11) NOT NULL DEFAULT '0',
    `pay_ok` int(11) NOT NULL DEFAULT '0',
    PRIMARY KEY (`item_id`,`record_id`),
    KEY `item_id` (`student_sn`)
) ENGINE=MyISAM ;


CREATE TABLE `charge_decrease` (
    `decrease_id` int(11) NOT NULL AUTO_INCREMENT,
    `detail_id` int(11) NOT NULL DEFAULT '0',
    `student_sn` int(11) NOT NULL DEFAULT '0',
    `curr_class_num` varchar(6) NOT NULL DEFAULT '',
    `decrease_dollar` float DEFAULT '0',
    `cause_chk` tinyint(4) NOT NULL DEFAULT '0',
    `modify_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `item_id` int(11) NOT NULL  ,
    `sit_num` tinyint(4) NOT NULL,
    `cause_other` int(11) NOT NULL DEFAULT '0',
    PRIMARY KEY (`detail_id`,`student_sn`,`curr_class_num`),
    UNIQUE KEY `decrease_id` (`decrease_id`)
) ENGINE=MyISAM ;


CREATE TABLE   `charge_account` (
    `a_id` bigint(20) NOT NULL AUTO_INCREMENT,
    `stud_sn` bigint(20) NOT NULL,
    `stud_name` varchar(30)   NOT NULL DEFAULT '',
    `acc_name` varchar(30)   NOT NULL DEFAULT '',
    `acc_person_id` varchar(12)   NOT NULL DEFAULT '',
    `acc_mode` char(1)   NOT NULL DEFAULT '',
    `acc_b_id` varchar(20)   NOT NULL DEFAULT '',
    `acc_id` varchar(20)   NOT NULL DEFAULT '',
    `acc_g_id` varchar(20)   NOT NULL DEFAULT '',
    PRIMARY KEY (`stud_sn`),
    KEY `a_id` (`a_id`)
) ENGINE=MyISAM  COMMENT='郵局扣款帳號';

CREATE TABLE  `charge_poster_data` (
    `item_id` int(11) NOT NULL,
    `t_id` varchar(20)   NOT NULL,
    `class_id` int(11) NOT NULL,
    `sit_num` int(11) NOT NULL,
    `st_name` varchar(30)   NOT NULL DEFAULT '',
    `pay` int(11) NOT NULL DEFAULT '0',
    `acc_name` varchar(30)   NOT NULL DEFAULT '',
    `acc_personid` varchar(20)   NOT NULL DEFAULT '',
    `acc_mode` varchar(10)   NOT NULL DEFAULT '',
    `acc_b_id` varchar(20)   NOT NULL DEFAULT '',
    `acc_id` varchar(20)   NOT NULL DEFAULT '',
    `acc_g_id` varchar(20)   NOT NULL DEFAULT '',
    `stud_else` int(11) NOT NULL DEFAULT '0',
    `cash` int(11) NOT NULL DEFAULT '0',
    `pay_fail` int(11) NOT NULL DEFAULT '0',
    PRIMARY KEY (`item_id`,`t_id`)
) ENGINE=MyISAM  ;

CREATE TABLE  `charge_bank_account` (
    `b_id` int(11) NOT NULL AUTO_INCREMENT,
    `account_name` varchar(80)  NOT NULL,
    `account1`  varchar(80)  NOT NULL,
    `account2`  varchar(80)  NOT NULL,
    `account_id`  varchar(10)  NOT NULL,
    `account_block_id`  varchar(10)  NOT NULL,
    `account_pay`  int(11) NOT NULL DEFAULT '0',
    `paper`  TEXT NOT NULL ,
    PRIMARY KEY (`b_id` )
) ENGINE=MyISAM  ;
