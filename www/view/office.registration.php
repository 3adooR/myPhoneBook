<form id="form" action="?go=registration" method="post">
    <div class="office-form">
        <h2>Регистрация</h2>
        <div class="gline">
            <div>E-mail:</div>
            <div><input class="email" name="u_mail" type="email" value="<?= $this->userFields['u_mail'] ?>" required/>
            </div>
        </div>
        <div class="gline">
            <div>Пароль:</div>
            <div><input name="u_pass" type="password" value="<?= $this->userFields['u_pass'] ?>" required/></div>
        </div>
        <div class="gline">
            <div>Имя:</div>
            <div><input name="u_name" type="text" value="<?= $this->userFields['u_name'] ?>"/></div>
        </div>
        <div class="gline">
            <div>Телефон:</div>
            <div><input class="phone" name="u_phone" type="tel" value="<?= $this->userFields['u_phone'] ?>"/></div>
        </div>
        <div class="gline">
            <div><img style="cursor:pointer" src="<?= $this->host ?>sys/lib/captha/captcha.php"
                      onclick="this.src='<?= $this->host ?>sys/lib/captha/captcha.php?a='+Math.random();"
                      alt="не понятно что написано"/></div>
            <div>введите код c картинки:<br/><input name="captcha" type="text" required/></div>
        </div>
        <div class="gline actions">
            <div><input class="btn" type="button" name="office-registration" value="Зарегистрироваться"/></div>
            <div>
                <div class="fRight"><a href="?go=remember"><i class="far fa-unlock"></i>Забыли пароль?</a></div>
                <a href="?go=auth"><i class="far fa-sign-in"></i> Авторизация</a>
            </div>
        </div>
    </div>
</form>