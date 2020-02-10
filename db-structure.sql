--
-- База данных
--

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE `users` (
  `u_id` int(10) NOT NULL,
  `u_login` varchar(100) NOT NULL DEFAULT '',
  `u_pass` varchar(100) NOT NULL DEFAULT '',
  `u_name` varchar(255) NOT NULL DEFAULT '',
  `u_mail` varchar(255) NOT NULL DEFAULT '',
  `u_phone` varchar(25) NOT NULL DEFAULT '',
  `u_datereg` datetime NOT NULL DEFAULT current_timestamp(),
  `u_lastvisit` datetime NOT NULL DEFAULT current_timestamp(),
  `u_visits` int(10) NOT NULL DEFAULT 0,
  `u_permission` int(1) NOT NULL DEFAULT 0,
  `u_ip` varchar(25) NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `users_book`
--

CREATE TABLE `users_book` (
  `b_id` int(10) NOT NULL,
  `b_user` int(11) NOT NULL DEFAULT 0,
  `b_name` varchar(255) NOT NULL DEFAULT '',
  `b_surname` varchar(255) NOT NULL DEFAULT '',
  `b_phone` varchar(25) NOT NULL DEFAULT '',
  `b_mail` varchar(255) NOT NULL DEFAULT '',
  `b_photo` varchar(512) NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`u_id`);
ALTER TABLE `users` ADD FULLTEXT KEY `u_login` (`u_login`,`u_pass`);
ALTER TABLE `users` ADD FULLTEXT KEY `u_mail` (`u_mail`);
ALTER TABLE `users` ADD FULLTEXT KEY `u_phone` (`u_phone`);

--
-- Индексы таблицы `users_book`
--
ALTER TABLE `users_book`
  ADD PRIMARY KEY (`b_id`),
  ADD KEY `b_user` (`b_user`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `u_id` int(10) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT для таблицы `users_book`
--
ALTER TABLE `users_book`
  MODIFY `b_id` int(10) NOT NULL AUTO_INCREMENT;