--
-- Table structure for table mailqueue
--

CREATE TABLE mailqueue (
  id serial primary key,
  state varchar(40) NOT NULL DEFAULT 'pending',
  sender_name varchar(128) DEFAULT NULL,
  sender_email varchar(320) NOT NULL,
  recipient_name varchar(128) DEFAULT NULL,
  recipient_email varchar(320) NOT NULL,
  subject varchar(78) DEFAULT NULL,
  priority smallint NOT NULL DEFAULT '1',
  attempts smallint NOT NULL DEFAULT '0',
  created timestamptz NOT NULL,
  sent timestamptz DEFAULT NULL,
  failed timestamptz DEFAULT NULL
  
);

create index  mailqueue_state ON mailqueue (state);
-- --------------------------------------------------------

--
-- Table structure for table mailqueue_bodies
--

CREATE TABLE mailqueue_bodies (
  id serial primary key,
  queue_id integer NOT NULL references mailqueue(id) on delete cascade,
  body text NOT NULL
);