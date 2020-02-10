<form id="form" action="?go=remember" method="post">
    <div class="office-form">
        <h2>Восстановление пароля</h2>
        <div class="gline">
            <div>E-mail:</div>
            <div><input class="email" name="u_mail" type="email" value="<?= $this->userFields['u_mail'] ?>" required/>
            </div>
        </div>
        <div class="gline">
            <div><img style="cursor:pointer" src="<?= $this->host ?>sys/lib/captha/captcha.php"
                      onclick="this.src='<?= $this->host ?>sys/lib/captha/captcha.php?a='+Math.random();"
                      alt="не понятно что написано"/></div>
            <div>введите код c картинки:<br/><input name="captcha" type="text" required/></div>
        </div>
        <div class="gline actions">
            <div><input class="btn" name="office-remember" value="Восстановить пароль" type="button"/></div>
            <div>
                <div class="fRight"><a href="?go=registration"><i class="far fa-list-alt"></i> Регистрация</a></div>
                <a href="?go=auth"><i class="far fa-sign-in"></i> Авторизация</a>
            </div>
        </div>
    </div>
</form>