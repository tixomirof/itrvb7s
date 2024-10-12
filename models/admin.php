<?php
class Admin extends User {
    public function echoMain() {
        ?>
        <main>
            <p>Действия администратора:</p>
            <ul>
                <li><a href="../not-implemented.php">Добавить товар</a></li>
                <li><a href="../not-implemented.php">Удалить товар</a></li>
            </ul>
        </main>
        <?php
    }
}
?>