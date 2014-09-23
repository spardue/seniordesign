<?php
/*
login in UI include
   */
require('header.php');
?>
<div class="container" style="margin-top:30px">
    <div class="col-md-4 col-md-offset-4">
        <div class="panel panel-default">
            <div class="panel-heading"><h3 class="panel-title"><strong>Please Sign In</strong></h3></div>
            <div class="panel-body">
                <form role="form" action="index.php" method="post">
                    <div class="form-group">
                        <label for="user_name">Username</label>
                        <input name="user_name" type="username" class="form-control" style="border-radius:0px"
                               id="user_name" placeholder="Username">
                    </div>
                    <div class="form-group">
                        <label for="user_password">Password</label>
                        <input name="user_password" type="password" class="form-control" style="border-radius:0px"
                               id="user_password" placeholder="Password">
                    </div>
                    <div class="form-group">
                        <label for="user_rememberme">Remember Me?</label>
                        <input type="checkbox" id="user_rememberme" name="user_rememberme" value="1"/>
                    </div>
                    <div class="form-group">
                        <input name="login" type="submit" class="btn btn-sm btn-default" value="Sign in"/>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>
