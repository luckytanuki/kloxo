<?php 

// by mustafa@bigraf.com for Kloxo-MR

include_once "htmllib/lib/include.php"; 

$mysqlbranch = getRpmBranchInstalled('mysql');

echo "*** Change MariaDB to MySQL - begin ***\n";

system("yum clean all");
system("sh /script/fix-service-list");
echo "\n";

if (strpos($mysqlbranch, "mysql") !== false) {
	echo "* Already '{$mysqlbranch}' installed\n";
} elseif (strpos($mysqlbranch, "MariaDB") !== false) {

	exec("yum list|grep MariaDB", $out, $ret);
	
	if ($ret) {
		echo "- Repo for MariaDB exists.\n";
		echo "  Open '/etc/yum.repos.d/kloxo-mr.repo and change 'enable=1' to 'enable=0'\n";
		echo "  under [kloxo-mr-mariadb32] for 32bit OS or [kloxo-mr-mariadb64] for 64bit OS\n";
	} else {
		// MR -- don't use $mysqlbranch because for MariaDB mean MariaDB-server
		$out2 = shell_exec("rpm -qa|grep MariaDB");

		$arr = explode("\n", $out2);

		echo "- Remove MariaDB packages\n";
		foreach ($arr as &$o) {
			system("rpm -e {$o} --nodeps");
		}

		echo "- Install MySQL\n";
		system("yum install mysql mysql-server -y");

		if (file_exists("/etc/my.cnf.d/my.cnf")) {
			system("cp -f /etc/my.cnf.d/my.cnf /etc/my.cnf");
		} elseif (file_exists("/etc/my.cnf._bck_")) {
			system("cp -f /etc/my.cnf._bck_ /etc/my.cnf");
		}

		echo "- Restart MySQL\n";
		system("chkconfig mysqld on");
		system("service mysqld restart");
	}
} else {
	echo "- No MySQL or MariaDB installed\n";
}

echo "\n";
echo " - Note: remove 'skip-innodb' from '/etc/my.cnf' and '/etc/my.cnf.d/my.cnf'.\n";
echo "   Need reboot!.\n\n";

echo "*** Change MariaDB to MySQL - end ***\n";
