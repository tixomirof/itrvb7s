<?php
class ContactForm {
    public function build() {
        ?>
        <form name='contact-form' action="./contact-form.php" method='POST'>
            <label for="email">Почта для обратной связи</label>
            <input type="email" name="email" id="email">
            <label for="text">Ваше сообщение</label>
            <textarea name="text" id="text"></textarea>
        </form>
        <a href="../index.php">Назад</a>
        <?php
    }

    public function action() {
        $email = $_POST['email'];
        $text = $_POST['text'];
        // ... add text to db
    }
}

$contactForm = new ContactForm();
$contactForm->build();

if (!empty($_POST)) {
    $contactForm->action();
}
?>