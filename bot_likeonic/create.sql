drop table if exists public.users CASCADE;
drop table if exists public.chat_state CASCADE;
drop table if exists public.chats CASCADE;
drop table if exists public.messages_history CASCADE;
drop table if exists public.faq CASCADE;
drop table if exists public.questions CASCADE;
drop table if exists public.answers CASCADE;
drop table if exists public.langs CASCADE;
drop table if exists public.log CASCADE;

-- Таблица пользователей, которые открыли нашего бота (данные берутся из сообщения телеграма)
CREATE TABLE public.users (
  -- id пользователя
  id integer UNIQUE,
  -- никнейм пользователя
  username text,
  -- имя пользователя
  name text,
  lang text,
  PRIMARY KEY (id)
)
WITH (
    OIDS = FALSE
);

ALTER TABLE public.users
    OWNER to postgres;

-- Состояние чата (инициализация, поиск ответа, нужна помощь человека, вопрос решен)
CREATE TABLE public.chat_state (
  -- id состояния чата
  id integer UNIQUE,
  -- описание состояния
  type text,
  PRIMARY KEY (id)
)
WITH (
    OIDS = FALSE
);

ALTER TABLE public.chat_state
    OWNER to postgres;


-- Таблица языков
CREATE TABLE public.langs (
  id integer UNIQUE,
  lang text,
  prom text,
  PRIMARY KEY (id)
)
WITH (
    OIDS = FALSE
);
ALTER TABLE public.langs
    OWNER to postgres;

-- -- Таблица вопросов и ответов
-- CREATE TABLE public.faq (
--   -- id вопрос-ответа
--   id integer UNIQUE,
--   -- id родителя
--   parent integer REFERENCES faq(id),
--   chat_state integer REFERENCES chat_state(id),
--   PRIMARY KEY (id)
-- )
-- WITH (
--     OIDS = FALSE
-- );


-- ALTER TABLE public.faq
--     OWNER to postgres;

-- Таблица вопросов
CREATE TABLE public.questions (
  id integer UNIQUE,
  lang integer REFERENCES langs(id),
  question text,
  chat_state integer REFERENCES chat_state(id),
  parent integer REFERENCES questions(id),
  child integer REFERENCES questions(id),
  PRIMARY KEY (id)
)
WITH (
    OIDS = FALSE
);

ALTER TABLE public.questions
    OWNER to postgres;

-- Таблица ответов
CREATE TABLE public.answers (
  id integer UNIQUE,
  lang integer REFERENCES langs(id),
  question integer REFERENCES questions(id),
  answer text,
  PRIMARY KEY (id)
)
WITH (
    OIDS = FALSE
);

ALTER TABLE public.answers
    OWNER to postgres;

-- Список всех чатов (!= списку пользователей, так как могут быть потенциально груповые чаты. Боту работать в них будет запрещено)
CREATE TABLE public.chats (
  -- id чата
  id SERIAL,
  -- состояние чата
  chat_id integer UNIQUE,
  chat_state integer REFERENCES chat_state(id),
  user_id integer REFERENCES users(id),
  current_question integer REFERENCES questions(id) not null,
  PRIMARY KEY (chat_id)
)
WITH (
    OIDS = FALSE
);

ALTER TABLE public.chat
    OWNER to postgres;

-- История сообщений пользователей (что бы можно было понять, что он пытался найти и помочь ему)
CREATE TABLE public.messages_history (
  -- Время сообщения
  timemark timestamp,
  -- id сообщения
  id SERIAL,
  -- текст сообщения
  message text,
  -- из какого чата сообщение
  chat_id integer REFERENCES chats(chat_id),
  -- от когого пользователя сообщение
  user_id integer REFERENCES users(id),
  bot_flag boolean DEFAULT 'false',
  PRIMARY KEY (id)
)
WITH (
    OIDS = FALSE
);

ALTER TABLE public.messages_history
    OWNER to postgres;

-- История сообщений пользователей (что бы можно было понять, что он пытался найти и помочь ему)
CREATE TABLE public.log (
  -- Время сообщения
  timemark timestamp,
  -- id сообщения
  id SERIAL,
  -- текст сообщения
  query text,
  error text,
  PRIMARY KEY (id)
)
WITH (
    OIDS = FALSE
);

ALTER TABLE public.log
    OWNER to postgres;

insert into chat_state (id,type) values (0,'lang');
insert into chat_state (id,type) values (1,'idle');
insert into chat_state (id,type) values (2,'general');
insert into chat_state (id,type) values (3,'web_app');
insert into chat_state (id,type) values (4,'app');
insert into chat_state (id,type) values (5,'staff_app');
insert into chat_state (id,type) values (6,'help');

insert into langs (id,lang,prom) values (1,'Русский','Пожалуйста, выберите ваш язык');
insert into langs (id,lang,prom) values (2,'English','Please select your language');
insert into langs (id,lang,prom) values (3,'Ελληνικά','Επιλέξτε τη γλώσσα σας');

-- insert into 

-- insert into faq (id,chat_state) values (0,1);
-- insert into faq (id,parent,chat_state) values (1,0,1);

insert into questions (id,lang,question,chat_state) values (1,1,'Добро пожаловать в чат-бота Likeonic!',1);
insert into questions (id,lang,question,chat_state,parent) values (2,1,'Вопросы по WEB приложению',1,1);
insert into questions (id,lang,question,chat_state,parent) values (3,1,'Вопросы по мобильному приложению',1,1);
insert into questions (id,lang,question,chat_state,parent) values (4,1,'Вопросы по приложению для персонала',1,1);
insert into questions (id,lang,question,chat_state,parent) values (5,1,'что то1',3,2);
insert into questions (id,lang,question,chat_state,parent) values (6,1,'что то2',3,2);



-- insert into faq (id,chat_state,question_ru,answer_ru) values (0,1,'Добро пожаловать в чат-бота Likeonic!','');

-- insert into faq (id,parent,chat_state,question_ru,answer_ru) values (1,0,1,'Общие вопросы','');
-- insert into faq (id,parent,chat_state,question_ru,answer_ru) values (2,0,1,'Вопросы по WEB приложению','');
-- insert into faq (id,parent,chat_state,question_ru,answer_ru) values (3,0,1,'Вопросы по мобильному приложению','');
-- insert into faq (id,parent,chat_state,question_ru,answer_ru) values (4,0,1,'Вопросы по приложению для персонала','');

-- insert into faq (id,parent,chat_state,question_ru,answer_ru) values (5,1,2,'Что такое Лайкоин','Лайкоин (LCO) – цифровая валюта лояльности, которую вы автоматически получаете в качестве кэшбэка за покупки и за выполнение заданий, а так же за участие в реферальной программе «Пригласи друга». Лайкоины поступают на ваш персональный аккаунту, где надёжно хранятся в системе без истечения срока действия и могут быть использованы при следующей оплате в партнерском магазине или продолжать накапливаться на более крупные покупки.\nКурс Лайкоина всегда стабилен: 1 LCO = 1 EUR ');
-- insert into faq (id,parent,chat_state,question_ru,answer_ru) values (6,1,2,'Как получить Лайкоины','Лайкоин (LCO) – цифровая валюта лояльности, которую вы автоматически получаете в качестве кэшбэка за покупки и за выполнение заданий, а так же за участие в реферальной программе «Пригласи друга». Лайкоины поступают на ваш персональный аккаунту, где надёжно хранятся в системе без истечения срока действия и могут быть использованы при следующей оплате в партнерском магазине или продолжать накапливаться на более крупные покупки.\nКурс Лайкоина всегда стабилен: 1 LCO = 1 EUR ');








