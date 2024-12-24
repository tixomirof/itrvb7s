-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Хост: 127.0.0.1:3306
-- Время создания: Дек 24 2024 г., 17:48
-- Версия сервера: 8.0.30
-- Версия PHP: 8.1.9

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `itrvb-7-semestr`
--

-- --------------------------------------------------------

--
-- Структура таблицы `articles`
--

CREATE TABLE `articles` (
  `uuid` varchar(36) NOT NULL,
  `author_id` varchar(36) NOT NULL,
  `header` varchar(512) NOT NULL,
  `text` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `articles`
--

INSERT INTO `articles` (`uuid`, `author_id`, `header`, `text`) VALUES
('1b42fe64-a369-47f7-a269-360a3f785e5c', '7a7c8f28-b7f6-4e19-9db8-366f7693ac9e', 'Ut aspernatur labore similique provident.', 'Enim eius quis error sunt impedit. Perferendis rerum a omnis ut est sapiente quae. Ex unde autem consequuntur magnam aut dolor. Hic nihil ducimus nemo voluptas ullam maiores voluptas. Modi sed consequatur enim non consequatur voluptatem facilis. Consectetur omnis enim consequatur. Fugit esse voluptatem nulla quia perferendis laborum et veritatis. Id voluptas doloribus dignissimos corporis fugiat enim. Numquam fuga nesciunt aut quod quam ab. Iusto perferendis magni consectetur unde repellendus eum. Est ad accusamus quibusdam ipsum quaerat. Veritatis est minus voluptas corporis voluptate. Porro nesciunt nam minus aut incidunt ducimus unde. Est corporis nisi architecto aliquam voluptas est porro. Qui deleniti in sunt inventore incidunt totam molestias. Quia minus praesentium quia. Mollitia et esse id esse. Dicta reprehenderit et qui blanditiis et ut expedita. Nostrum dignissimos magnam consequatur quasi molestiae ab. Quae aliquam libero voluptatem similique. Et et et totam aliquam officia quam rem. Accusamus id distinctio et odio molestias ut. Rerum quod ut et pariatur blanditiis eligendi at consequatur. Et impedit quis architecto architecto. Quis quasi et voluptatem nam. Et omnis qui hic nesciunt ea est dolor sapiente. Est enim earum aut expedita exercitationem et dolorem. Ducimus ut ut perspiciatis excepturi ut id perspiciatis. Nemo ratione deserunt odio quaerat cupiditate. Et quam laudantium consequuntur iste quae. Illo natus non repellendus consequatur et et rerum. Necessitatibus recusandae nihil aut et voluptatem. Voluptas et possimus quam perspiciatis delectus. Excepturi vitae inventore animi eos ullam. Sint similique amet molestiae architecto consectetur praesentium tempore. Libero quos maiores aut rerum. Mollitia corrupti et tempore ratione iure odit. Quae architecto qui cupiditate ex nemo. Accusantium architecto natus velit aliquam sint. Dolorum sit possimus omnis magni est suscipit illum. Sed ipsam ab totam voluptatem quia. Numquam beatae molestiae esse voluptates adipisci. Dignissimos eos blanditiis fugiat earum perspiciatis reprehenderit. Quam rem et eos repellendus. Eos dolor aut voluptatem molestiae qui. Rerum nisi pariatur incidunt autem sint vitae. Aliquam qui corporis voluptate nostrum enim quasi sed. Inventore eum nihil ut aliquid quia et. Explicabo id aut minima fugit expedita. Dicta dolorem molestiae et modi reprehenderit. Distinctio perspiciatis hic commodi unde et. Aut quis officia quia molestias. Et molestias saepe ut aut. Temporibus et ut similique laborum saepe qui. Aspernatur rerum velit sed ut atque. Libero laboriosam rem pariatur minus reprehenderit repellat dignissimos. Eius deserunt ipsa velit et et omnis et. Id est rerum et sit quo. Itaque sint et modi labore eveniet enim. Maiores magni nulla voluptatibus ipsa beatae voluptas. Officia laudantium quo reiciendis facere. Recusandae voluptatem cumque culpa quaerat vel corrupti. Tenetur commodi ex ullam cumque.');

-- --------------------------------------------------------

--
-- Структура таблицы `comments`
--

CREATE TABLE `comments` (
  `uuid` varchar(36) NOT NULL,
  `author_id` varchar(36) NOT NULL,
  `article_id` varchar(36) NOT NULL,
  `text` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `comments`
--

INSERT INTO `comments` (`uuid`, `author_id`, `article_id`, `text`) VALUES
('18518102-e874-40b6-8a0b-c554f1bf867c', '273a1d82-3c33-4afa-a833-2a800f8d4d3a', '1b42fe64-a369-47f7-a269-360a3f785e5c', 'Harum qui iste vel magnam eveniet quia magni.'),
('3398726f-26aa-4a18-bb7f-d4264f1a53a2', '1e54e6a0-6844-45a9-934d-9ec512ec7fc8', '1b42fe64-a369-47f7-a269-360a3f785e5c', 'Cumque eum placeat natus expedita nam consectetur optio. Maiores nesciunt qui est veniam consequatur unde. Numquam voluptatem voluptatibus consequatur modi deserunt nobis voluptatem. Quia voluptatem saepe voluptatem ex nesciunt eum ut. Dicta et et exercitationem qui. Voluptatibus ipsum explicabo voluptas soluta quibusdam earum error. Velit nobis qui at deleniti. Ut ab velit dolores tenetur id. Tempora debitis aut vitae tempore reiciendis alias ratione. A ullam quo ab aspernatur. Harum et ut sed voluptates exercitationem et incidunt. Sint dignissimos neque ullam voluptatem voluptatum hic. Ut aut est aperiam.'),
('9baa4b73-b756-476e-a4fb-63bd5d0d52bc', '273a1d82-3c33-4afa-a833-2a800f8d4d3a', '1b42fe64-a369-47f7-a269-360a3f785e5c', 'Magnam non tempore quae ex et omnis ipsam. Odio numquam architecto at quia. Nihil amet pariatur veritatis fugiat ut. Ut neque ut ut quisquam velit quis aperiam. Labore distinctio rerum est voluptatem voluptatem. Voluptatibus molestiae non quo consequatur. Fugiat amet in sint fugit impedit ipsa quia.');

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE `users` (
  `uuid` varchar(36) NOT NULL,
  `name` varchar(100) NOT NULL,
  `surname` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Таблица пользователей (Лаб4)';

--
-- Дамп данных таблицы `users`
--

INSERT INTO `users` (`uuid`, `name`, `surname`) VALUES
('1e54e6a0-6844-45a9-934d-9ec512ec7fc8', 'Eriberto', 'Macejkovic'),
('273a1d82-3c33-4afa-a833-2a800f8d4d3a', 'Jazmyne', 'Schuster'),
('3c06db8f-5340-4b5b-96a0-f30ab4f3272b', 'Carol', 'McDermott'),
('40bf6f35-f8b0-422f-a20d-bf8bb3ffa1d8', 'Terry', 'Cormier'),
('471547b5-c623-4565-8083-72bf71b381b2', 'Sadie', 'Adams'),
('7977916e-f27b-4f69-bfe2-967dc82f58b0', 'Alena', 'Veum'),
('7a7c8f28-b7f6-4e19-9db8-366f7693ac9e', 'Carlie', 'Jones'),
('7eea8379-9b52-4959-8095-efb6cf63f3df', 'Bettie', 'Bogan'),
('9d8d295f-b9fe-42ca-b107-358dc5b4ca41', 'Gudrun', 'Breitenberg'),
('deac3983-6459-4272-a9c9-5cc858bcdb14', 'Elda', 'Roberts');

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `articles`
--
ALTER TABLE `articles`
  ADD PRIMARY KEY (`uuid`),
  ADD KEY `articles_FK_1` (`author_id`);

--
-- Индексы таблицы `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`uuid`),
  ADD KEY `comments_FK_1` (`author_id`),
  ADD KEY `comments_FK_2` (`article_id`);

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`uuid`);

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `articles`
--
ALTER TABLE `articles`
  ADD CONSTRAINT `articles_FK_1` FOREIGN KEY (`author_id`) REFERENCES `users` (`uuid`);

--
-- Ограничения внешнего ключа таблицы `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_FK_1` FOREIGN KEY (`author_id`) REFERENCES `users` (`uuid`),
  ADD CONSTRAINT `comments_FK_2` FOREIGN KEY (`article_id`) REFERENCES `articles` (`uuid`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
