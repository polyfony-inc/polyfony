CREATE TABLE IF NOT EXISTS "Accounts" (
  "id" integer NULL PRIMARY KEY AUTOINCREMENT,
  "id_level" numeric NULL,
  "is_enabled" numeric NULL,
  "creation_date" numeric NULL,
  "login" text NULL,
  "password" text NULL,
  "modules_array" text NULL,
  "account_expiration_date" numeric NULL,
  "session_expiration_date" numeric NULL,
  "session_key" text NULL,
  "last_login_origin" text NULL,
  "last_login_agent" text NULL,
  "last_login_date" numeric NULL,
  "last_failure_origin" numeric NULL,
  "last_failure_agent" numeric NULL,
  "last_failure_date" numeric NULL
);

CREATE TABLE IF NOT EXISTS "Logs" (
  "id" integer NULL PRIMARY KEY AUTOINCREMENT,
  "id_level" integer NULL,
  "creation_date" numeric NULL,
  "login" text NULL,
  "bundle" text NULL,
  "controller" text NULL,
  "method" text NULL,
  "url" text NULL,
  "ip" text NULL,
  "agent" text NULL,
  "message" text NULL
);

CREATE TABLE IF NOT EXISTS "Mails" (
  "id" integer NULL PRIMARY KEY AUTOINCREMENT,
  "is_sent" numeric NULL,
  "creation_date" numeric NULL,
  "sending_date" numeric NULL,
  "title" text NULL,
  "format" text NULL,
  "from_mail" text NULL,
  "from_name" text NULL,
  "body" text NULL,
  "subject" text NULL,
  "bcc_array" text NULL,
  "cc_array" text NULL,
  "to_array" text NULL,
  "files_array" text NULL
);

CREATE TABLE IF NOT EXISTS "Store" (
  "id" text NULL,
  "key" text NULL,
  "content" blob NULL
);

VACUUM;