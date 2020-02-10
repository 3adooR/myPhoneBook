<div id="exit">
    <a href="?go=exit"><i class="far fa-sign-out-alt"></i> выход</a>
</div>
<form id="form" action="?go=list-edit&id=<?= $this->edit ?>" method="post" enctype="multipart/form-data">
    <div class="office-form">
        <h2>Редактирование записи</h2>
        <div class="gline">
            <div>Имя:</div>
            <div><input name="b_name" type="text" value="<?= $this->userFields['b_name'] ?>"/></div>
        </div>
        <div class="gline">
            <div>Фамилия:</div>
            <div><input name="b_surname" type="text" value="<?= $this->userFields['b_surname'] ?>"/></div>
        </div>
        <div class="gline">
            <div>Телефон:</div>
            <div><input class="phone" name="b_phone" type="tel" value="<?= $this->userFields['b_phone'] ?>"/></div>
        </div>
        <div class="gline">
            <div>Email:</div>
            <div><input name="b_mail" type="email" value="<?= $this->userFields['b_mail'] ?>"/></div>
        </div>
        <div class="gline">
            <div>Фото:</div>
            <div>
                <input id="b_photo" name="b_photo" type="hidden" value="<?= $this->userFields['b_photo'] ?>"/>
                <input type="hidden" name="MAX_FILE_SIZE" value="2097152"/>
                <input type="file" id="file" onchange="uploadFile('<?= $this->edit ?>')"/>
                <div class="preview">
                    <img id="img" src="<?= $this->host . 'upload/' . $this->userFields['b_photo'] ?>" alt="">
                </div>
            </div>
        </div>
        <div class="gline actions">
            <div><input class="btn" type="button" name="office-list-edit" value="Сохранить"/></div>
            <div>
                <div class="fRight"><a href="?go=list"><i class="far fa-list"></i> показать все записи</a></div>
            </div>
        </div>
    </div>
</form>