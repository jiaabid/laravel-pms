<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
class CreateUserChildProcedures extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
$user_child_procedure = "
DROP PROCEDURE IF EXISTS `user_childs`;

CREATE PROCEDURE `user_childs`(IN userId bigint)
BEGIN
with recursive role_childs(id,created_by) as (
select id,created_by from users where created_by= userId
union all
select users.id  from user_childs as uc join users on uc.id = users.created_by
)
select id from user_childs;

END
";


        DB::unprepared($user_child_procedure);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_child_procedures');
    }
}
