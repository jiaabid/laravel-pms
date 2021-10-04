<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RoleChildProcedure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $role_child_procedure = "
        DROP PROCEDURE IF EXISTS `role_childs`;

        CREATE PROCEDURE `role_childs`(IN roleId bigint)
      BEGIN
with recursive role_childs(id,name,parent) as (
    select id,name,parent from roles where parent = roleId
    union all
    select roles.id , roles.name,roles.parent from role_childs as rc join roles on rc.id = roles.parent
)
select * from role_childs;

END
        ";


        DB::unprepared($role_child_procedure);

        // WITH tblChild AS
        // (
        //     SELECT *
        //         FROM roles WHERE parent = roleId
        //     UNION ALL
        //     SELECT roles.* FROM roles  JOIN tblChild  ON roles.parent = tblChild.id
        // )
        // SELECT *
        //     FROM tblChild
        // OPTION(MAXRECURSION 32767)
        // WITH recursive childRoles (id,name,parent) AS
        // (
        //     SELECT *
        //         FROM roles WHERE roles.parent= roleId
        //     UNION ALL
        //     SELECT roles.* FROM roles  JOIN childRoles  ON roles.parent = childRoles.id
        // )
        //        SELECT * FROM childRoles;



    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
