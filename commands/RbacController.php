<?php

namespace app\commands;

use app\models\User;
use app\rbac\rules\AuthorRule;
use Yii;
use yii\console\Controller;
use yii\helpers\Console;

class RbacController extends Controller
{
    /**
     * Initializes the RBAC authorization data.
     */
    public function actionInit()
    {
        $auth = Yii::$app->authManager;

        //---------- RULES ----------//

        // add the rule
        $rule = new AuthorRule();
        $auth->add($rule);

        //---------- PERMISSIONS ----------//


        // add "adminData" permission
        $basicPerm = $auth->createPermission('basic');
        $basicPerm->description = 'Allows User to do basic things!';
        $auth->add($basicPerm);


        //---------- ROLES ----------//

        // add "member" role
        $member = $auth->createRole('member');
        $member->description = 'Authenticated user, equal to "@"';
        $auth->add($member);


        // add "admin" role
        $admin = $auth->createRole('admin');
        $admin->description = 'Administrator of this application';
        $auth->add($admin);
        $auth->addChild($admin, $basicPerm);

        // add "theCreator" role ( this is you :) )
        // You can do everything that admin can do plus more (if You decide so)
        $theCreator = $auth->createRole('theCreator');
        $theCreator->description = 'You!';
        $auth->add($theCreator);
        $auth->addChild($theCreator, $admin);

        if ($auth) {
            $this->stdout("\nRbac authorization data are installed successfully.\n", Console::FG_GREEN);
        }


        /*
         * create init user
         * login : root
         * password : ChangeThisPassw0rdTo0
         */

        $user = new User();
        $user->username = 'root';
        $user->email = "Change@this.mail";
        $user->setPassword('ChangeThisPassw0rdTo0');
        $user->generateAuthKey();
        $user->status = User::STATUS_ACTIVE;
        $user->created_at = $user->updated_at = time();

        // if user is saved and role is assigned return user object
        $user->save();
        $role = $auth->getRole('theCreator');
        $auth->assign($role, $user->id);
    }
}