<form id="form" action="?go=auth" method="post">
    <div class="office-form">
        <h2>Авторизация</h2>
        <div class="gline">
            <div>E-mail:</div>
            <div><input class="email" name="office-login" type="email" required/></div>
        </div>
        <div class="gline">
            <div>Пароль:</div>
            <div><input name="office-password" type="password" required/></div>
        </div>
        <div class="gline actions">
            <div><input class="btn" type="button" name="office-auth" value="Вход"/></div>
            <div>
                <div class="fRight"><a href="?go=remember"><i class="far fa-unlock"></i> Забыли пароль?</a></div>
                <a href="?go=registration"><i class="far fa-list-alt"></i> Регистрация</a>
            </div>
        </div>
    </div>
</form>