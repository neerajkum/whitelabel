CREATE TABLE CONSUMER (
	CONSUMER_ID INT(20) AUTO_INCREMENT,
        NAME VARCHAR(200)  NOT NULL,
        PHONE_NUM CHAR(10)  NOT NULL,
        EMAIL_ADDRESS VARCHAR(100)  NOT NULL,
        PASSWORD VARCHAR(200) NOT NULL,
        BIRTH_DATE DATE,
        HEIGHT CHAR (3),
        WEIGHT CHAR (3),
        ADDRESS VARCHAR (200),
        GENDER VARCHAR (10),
        CREATED_DT DATETIME,
        LAST_UPDATED_DT DATETIME,
        PRIMARY KEY (CONSUMER_ID)
);

CREATE TABLE CONSUMER_LOGIN (
	CONSUMER_LOGIN_ID INT(20) AUTO_INCREMENT,
	CONSUMER_ID INT(20),
        AUTH_KEY VARCHAR(128)  NOT NULL,
        PUSH_DEVICE_ID VARCHAR(1000)  NOT NULL,
        DEVICE_OS VARCHAR(100)  NOT NULL,
        OS_VERSION VARCHAR(200) NOT NULL,
        LOGIN_DATE DATETIME,
        LOGOUT_DATE DATETIME,
        IP_ADDRESS VARCHAR(20),
        PRIMARY KEY (CONSUMER_LOGIN_ID)
);
CREATE TABLE CITY_RATE (
	
        CITY_NAME VARCHAR(200)  NOT NULL,
        LATITUDE CHAR(10)  NOT NULL,
        LONGITUDE VARCHAR(100)  NOT NULL,
        RATE INT(20) NOT NULL
        
);

CREATE TABLE CONSUMER_SCHEDULE (
	SCHEDULE_ID INT(20) AUTO_INCREMENT,
	PROFILE VARCHAR(11),
	CONSUMER_ID INT(20),
	PROVIDER_ID INT(20),
    TYPE_OF_SERVICE VARCHAR(50) NOT NULL DEFAULT 'YOGA',
    VENUE VARCHAR(100) NOT NULL,
    VENUE_LAT VARCHAR(20) NOT NULL,
    VENUE_LONG VARCHAR(20) NOT NULL,
    START_DATE DATE NOT NULL,
    END_DATE DATE NOT NULL,
	MON CHAR(1) NOT NULL DEFAULT 'N',
	TUE CHAR(1) NOT NULL DEFAULT 'N',
	WED CHAR(1) NOT NULL DEFAULT 'N',
	THURS CHAR(1) NOT NULL DEFAULT 'N',
	FRI CHAR(1) NOT NULL DEFAULT 'N',
	SAT CHAR(1) NOT NULL DEFAULT 'N',
	SUN CHAR(1) NOT NULL DEFAULT 'N',
    PRIMARY KEY (SCHEDULE_ID)
    );
	
	CREATE TABLE CONSUMER_SCHEDULE_DATE (
	SCHEDULE_DATE_ID INT(20) AUTO_INCREMENT,
	SCHEDULE_ID INT(20),
    SCHEDULE_DATE DATE NOT NULL,
    START_TIME TIME NOT NULL,
    END_TIME TIME NOT NULL,
    CLASS_STATUS BOOLEAN NOT NULL,
    PRIMARY KEY (SCHEDULE_DATE_ID)
);

CREATE TABLE CONSUMER_PROVIDER_MAP (
    CONSUMER_PROVIDER_MAP_ID INT(20) AUTO_INCREMENT,
    CONSUMER_SCHEDULE_ID INT(20),
	CONSUMER_ID INT(20),
    PROVIDER_ID INT(20),
    MAP_STATUS VARCHAR(20),
    PRIMARY KEY (CONSUMER_PROVIDER_MAP_ID)
);
CREATE TABLE PROVIDER (
	PROVIDER_ID INT(20) AUTO_INCREMENT,
        NAME VARCHAR(200)  NOT NULL,
        PHONE_NUM CHAR(10)  NOT NULL,
        EMAIL_ADDRESS VARCHAR(100)  NOT NULL,
        PASSWORD VARCHAR(200) NOT NULL,
        BIRTH_DATE DATE,
        HEIGHT CHAR (3),
        WEIGHT CHAR (3),
        ADDRESS VARCHAR (200),
        GENDER VARCHAR (10),
        EXPERIENCE VARCHAR(30),
        CREATED_DT DATETIME,
        LAST_UPDATED_DT DATETIME,
        PRIMARY KEY (PROVIDER_ID)
);
CREATE TABLE PROVIDER_LOGIN (
	PROVIDER_LOGIN_ID INT(20) AUTO_INCREMENT,
	PROVIDER_ID INT(20),
        AUTH_KEY VARCHAR(128)  NOT NULL,
        PUSH_DEVICE_ID VARCHAR(1000)  NOT NULL,
        DEVICE_OS VARCHAR(100)  NOT NULL,
        OS_VERSION VARCHAR(200) NOT NULL,
        LOGIN_DATE DATETIME,
        LOGOUT_DATE DATETIME,
        IP_ADDRESS VARCHAR(20),
        PRIMARY KEY (PROVIDER_LOGIN_ID)
);
CREATE TABLE QUALIFICATION (
    QUALIFICATION_ID INT(20) AUTO_INCREMENT,
    PROVIDER_ID INT(20),
    DEGREE VARCHAR(50),
    INSTITUTE VARCHAR(100),
    COURSE_TYPE VARCHAR(30),
    BATCH_START INT(4),
    BATCH_END INT(4), 
	PRIMARY KEY (QUALIFICATION_ID)
	);
	
	CREATE TABLE PROMO_TABLE
( PROMO_ID INT(20) AUTO_INCREMENT,
PROMO_CODE VARCHAR(50),
AMOUNT_DISCOUNT INT(10),
PERCENT_DISCOUNT INT(10),
PRIMARY KEY (PROMO_ID) );