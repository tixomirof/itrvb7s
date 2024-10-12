<?php
class User {
    function __construct(string $login, string $password) {
        if ($login <> 'login' || $password <> '123') {
            ob_end_clean();
            $this->showLoginError();
        }
        else {
            $this->connected = true;
            $this->username = 'Микрочел Микрочеликович';
            $this->cart = new Cart($this);
            $this->cart->addProduct(new Product('Мороженое', 59.99, 'Пломбирное мороженое'));
            $this->balance = 150.00;
        }
    }

    public bool $connected = false;
    public string $username;
    public Cart $cart;
    public float $balance;

    private function showLoginError() {
        ?>
        <div class='error' style='background-color:lightgray; color:darkred;
         border:5px solid red; text-align:center; padding:15px; vertical-align:middle;'>
            <h3>Ошибка входа!</h3>
            <p>Неверно указан пароль или логин.</p>
        </div>
        <?php
    }

    public function echoMain() {
        ?>
        <main>
            <?php $this->cart->show() ?>
        </main>
        <?php
    }

    public function showProfile() {
        if (!$this->connected) {
            ob_end_clean();
            $this->showLoginError();
            return;
        }
        ?>
        <header>
            <span>Личный кабинет</span>
            <span><?php echo $this->username ?></span>
        </header>
        <?php $this->echoMain() ?>
        <footer>
            <span>ВСЕ ПРАВА ЗАЩИЩЕНЫ ПРАВОВСКИМ ПРАВОМ, ПО ПРАВУ (СССССС)</span>
            <a href="./contact-form.php">Контактная форма</a>
        </footer>
        <?php
    }

    public function buyCart() {
        $cost = $this->cart->getTotalCost();
        ob_end_clean();
        if ($cost <= $this->balance) {
            $this->cart->clear();
            ?>
            <div class="success">
                <p>Поздравляем вас с покупкой!</p>
            </div>
            <?php
        } else {
            ?>
            <div class="error">
                <p>На балансе недостаточно средств.</p>
            </div>
            <?php
        }
        ?>
        <a href="../index.php">Назад</a>
        <?php
    }
}
?>