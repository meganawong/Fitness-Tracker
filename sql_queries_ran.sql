drop table DAILYSTATISTIC cascade constraints;
drop table EXERCISE cascade constraints;
drop table GYMSESSION_EXERCISE cascade constraints;
drop table EXERCISEGROUP cascade constraints;
drop table FOODITEM cascade constraints;
drop table FOODLOG cascade constraints;
drop table FOODLOG_LOG cascade constraints;
drop table GROUPEDPERSON cascade constraints;
drop table GYMLOCATION cascade constraints;
drop table GYMSESSION cascade constraints;
drop table PERSON cascade constraints;
drop table POSTALCODE cascade constraints;
drop table STRENGTHGOAL cascade constraints;


CREATE TABLE Person
(
    id char(10),
    name char(20),
    address char(40),
    postal_code char(7),
    start_date date,
    sex char(1),
    start_weight real,
    PRIMARY KEY(id)
);

INSERT INTO Person
    (id, name, address, postal_code, start_date, sex, start_weight)
VALUES('16b23a2k4b', 'John Smith', '2525 West Mall', 'V6T1W9', TO_DATE('2020-11-29','YYYY-MM-DD') , 'M', 210.4);

INSERT INTO Person
    (id, name, address, postal_code, start_date, sex, start_weight)
VALUES('17bsa2vkf2', 'Cameron Smith', '2525 West Mall', 'V6T1W9', TO_DATE('2020-11-29','YYYY-MM-DD') , 'M', 200.4);

INSERT INTO Person
    (id, name, address, postal_code, start_date, sex, start_weight)
VALUES('23asa3vkff', 'Ken Jones', '2525 West Mall', 'V6T1W9', TO_DATE('2020-11-29','YYYY-MM-DD') , 'M', 220.1);

INSERT INTO Person
    (id, name, address, postal_code, start_date, sex, start_weight)
VALUES('2la02kaj23', 'Angelina Jones', '5278 Kingsway ', 'V5H2E9', TO_DATE('2020-12-02','YYYY-MM-DD') , 'M', 102.3);


CREATE TABLE PostalCode
(
    postal_code char(7),
    province char(2),
    PRIMARY KEY(postal_code)
);

INSERT INTO PostalCode
    (postal_code, province)
VALUES('V6T1W9', 'BC');


CREATE TABLE ExerciseGroup
(
		group_id char(8),
		group_name char(30),
		PRIMARY KEY(group_id));

INSERT INTO ExerciseGroup
    (group_id, group_name)
VALUES 
    ('b8d9f2m0', 'group 1');
INSERT INTO ExerciseGroup
    (group_id, group_name)
VALUES 
    ('j1s0c3m8', 'group 2');
INSERT INTO ExerciseGroup
    (group_id, group_name)
VALUES 
    ('x5b1m3f9', 'group 3');
INSERT INTO ExerciseGroup
    (group_id, group_name)
VALUES 
    ('l2w9a7p1', 'group 4');
INSERT INTO ExerciseGroup
    (group_id, group_name)
VALUES 
    ('a3n6k2m8', 'group 5');


CREATE TABLE GroupedPerson(
		group_id char(8),
		person_id char(10),
		PRIMARY KEY(group_id, person_id),
		FOREIGN KEY(group_id) REFERENCES ExerciseGroup(group_id),
		FOREIGN KEY(person_id) REFERENCES Person(id)
);

INSERT INTO GroupedPerson
    (group_id, person_id)
VALUES 
    ('b8d9f2m0', '16b23a2k4b');
INSERT INTO GroupedPerson
    (group_id, person_id)
VALUES 
    ('j1s0c3m8', '16b23a2k4b');
INSERT INTO GroupedPerson
    (group_id, person_id)
VALUES 
    ('x5b1m3f9', '16b23a2k4b');
INSERT INTO GroupedPerson
    (group_id, person_id)
VALUES 
    ('b8d9f2m0', '17bsa2vkf2');
INSERT INTO GroupedPerson
    (group_id, person_id)
VALUES 
    ('j1s0c3m8', '17bsa2vkf2');
INSERT INTO GroupedPerson
    (group_id, person_id)
VALUES 
    ('x5b1m3f9', '17bsa2vkf2');
INSERT INTO GroupedPerson
    (group_id, person_id)
VALUES 
    ('l2w9a7p1', '17bsa2vkf2');
INSERT INTO GroupedPerson
    (group_id, person_id)
VALUES 
    ('b8d9f2m0', '23asa3vkff');
INSERT INTO GroupedPerson
    (group_id, person_id)
VALUES 
    ('j1s0c3m8', '23asa3vkff');
INSERT INTO GroupedPerson
    (group_id, person_id)
VALUES 
    ('x5b1m3f9', '23asa3vkff');
INSERT INTO GroupedPerson
    (group_id, person_id)
VALUES 
    ('j1s0c3m8', '2la02kaj23');
INSERT INTO GroupedPerson
    (group_id, person_id)
VALUES 
    ('x5b1m3f9', '2la02kaj23');


CREATE TABLE DailyStatistic
(
    stat_id char(12),
    weight real,
    stat_date date,
    person_id char(10) not null,
    UNIQUE(person_id, stat_date),
    PRIMARY KEY(stat_id),
    FOREIGN KEY (person_id) REFERENCES Person(id) ON DELETE CASCADE
);

INSERT INTO DailyStatistic
    (stat_id, weight, stat_date, person_id)
VALUES
    ('3a2g8s52g4', 210.5, TO_DATE('2021-11-23','YYYY-MM-DD'), '16b23a2k4b');

    INSERT INTO DailyStatistic
    (stat_id, weight, stat_date, person_id)
VALUES
    ('9a92jf74s6f', 209.5, TO_DATE('2021-11-24','YYYY-MM-DD'), '16b23a2k4b');

    INSERT INTO DailyStatistic
    (stat_id, weight, stat_date, person_id)
VALUES
    ('1k245j3h3l', 209.2, TO_DATE('2021-11-25','YYYY-MM-DD'), '16b23a2k4b');

    INSERT INTO DailyStatistic
    (stat_id, weight, stat_date, person_id)
VALUES
    ('5v5d32a2o2', 208.9, TO_DATE('2021-11-26','YYYY-MM-DD'), '16b23a2k4b');

    INSERT INTO DailyStatistic
    (stat_id, weight, stat_date, person_id)
VALUES
    ('1d3f3dsa23', 208.5, TO_DATE('2021-11-27','YYYY-MM-DD'), '16b23a2k4b');


CREATE TABLE GymSession
(
    session_id char(5),
    end_time char(10),
    start_time char(10),
    session_date date,
    stat_id char(12) not null,
    gym_name char(30),
    gym_address char(30),
    PRIMARY KEY (session_id),
    FOREIGN KEY (stat_id) REFERENCES DailyStatistic(stat_id) ON DELETE CASCADE
);

INSERT INTO GymSession
    (session_id, end_time, start_time, session_date, stat_id, gym_name, gym_address)
VALUES
    ('1k53g', '11:30', '09:00', TO_DATE('2021-11-23','YYYY-MM-DD'), '3a2g8s52g4', 'Anytime Fitness', '489 W 2nd Ave');

INSERT INTO GymSession
    (session_id, end_time, start_time, session_date, stat_id, gym_name, gym_address)
VALUES
    ('1r45s', '1:30', '09:00', TO_DATE('2021-11-24','YYYY-MM-DD'), '9a92jf74s6f', 'Anytime Fitness', '489 W 2nd Ave');

INSERT INTO GymSession
    (session_id, end_time, start_time, session_date, stat_id, gym_name, gym_address)
VALUES
    ('5h09x', '09:30', '07:00', TO_DATE('2021-11-25','YYYY-MM-DD'), '1k245j3h3l', 'Anytime Fitness', '489 W 2nd Ave');

INSERT INTO GymSession
    (session_id, end_time, start_time, session_date, stat_id, gym_name, gym_address)
VALUES
    ('3h45l', '5:30', '3:00', TO_DATE('2021-11-26','YYYY-MM-DD'), '5v5d32a2o2', 'Golds Gym', '2155 Allison Rd');

INSERT INTO GymSession
    (session_id, end_time, start_time, session_date, stat_id, gym_name, gym_address)
VALUES
    ('7b45j', '3:30', '1:00', TO_DATE('2021-11-27','YYYY-MM-DD'), '1d3f3dsa23', 'Club 16', '16050 24 Ave #135');


CREATE TABLE GymLocation
(
    gym_loc_address char(30),
    gym_location char(20),
    PRIMARY KEY(gym_loc_address)
);

INSERT INTO GymLocation
    (gym_loc_address, gym_location)
VALUES
    ('489 W 2nd Ave', 'North Burnaby');
INSERT INTO GymLocation
    (gym_loc_address, gym_location)
VALUES
    ('2155 Allison Rd', 'UBC');
INSERT INTO GymLocation
    (gym_loc_address, gym_location)
VALUES
    ('16050 24 Ave #135', 'White Rock');


CREATE TABLE FoodItem
(
    food_name char(30),
    calories integer,
    meal_type char(15),
    PRIMARY KEY(food_name, calories)
);

INSERT INTO FoodItem
    (food_name, calories, meal_type)
VALUES
    ('Ham Omelette', 260, 'Breakfast');

INSERT INTO FoodItem
    (food_name, calories, meal_type)
VALUES
    ('Eggs Benedict', 460, 'Breakfast');

INSERT INTO FoodItem
    (food_name, calories, meal_type)
VALUES
    ('Black Coffee', 0, 'Breakfast');

INSERT INTO FoodItem
    (food_name, calories, meal_type)
VALUES('Shrimp Fried Rice', 600, 'Dinner');

INSERT INTO FoodItem
    (food_name, calories, meal_type)
VALUES('Apple', 80, null);


CREATE TABLE FoodLog
(
    log_id char(10),
    log_date date,
    stat_id char(12) NOT NULL,
    PRIMARY KEY(log_id),
    UNIQUE(stat_id),
    FOREIGN KEY(stat_id) REFERENCES DailyStatistic(stat_id) ON DELETE CASCADE
);

INSERT INTO FoodLog
    (log_id, log_date, stat_id)
VALUES
    ('1a723dys73', TO_DATE('2021-11-23', 'YYYY-MM-DD'), '3a2g8s52g4');


CREATE TABLE FoodLog_Log
(
    log_id char(10),
    food_name char(30),
    calories int,
    time_of_consumption char(5),
    PRIMARY KEY(log_id, food_name, calories),
    FOREIGN KEY(log_id) REFERENCES FoodLog(log_id),
    FOREIGN KEY(food_name, calories) REFERENCES FoodItem(food_name, calories) ON DELETE CASCADE
);

INSERT INTO FoodLog_Log
    (log_id, food_name, calories, time_of_consumption)
VALUES
    ('1a723dys73', 'Shrimp Fried Rice', 600, '11:05');

INSERT INTO FoodLog_Log
    (log_id, food_name, calories, time_of_consumption)
VALUES
    ('1a723dys73', 'Apple', 80, '01:06');


CREATE TABLE Exercise
(
    e_name char(35),
    e_type char(20),
    PRIMARY KEY(e_name)
);

INSERT INTO Exercise
    (e_name, e_type)
VALUES
    ('squat', 'legs');

INSERT INTO Exercise
    (e_name, e_type)
VALUES
    ('bench press', 'legs');

INSERT INTO Exercise
    (e_name, e_type)
VALUES
    ('deadlift', 'back');


CREATE TABLE StrengthGoal
(
    goal_id char(6),
    person_id char(10),
    e_name char(35) NOT NULL,
    start_reps int,
    start_sets int,
    start_weight real,
    goal_reps int,
    goal_sets int,
    goal_weight real,
    PRIMARY KEY(goal_id, person_id),
    FOREIGN KEY(e_name) REFERENCES Exercise(e_name) ON DELETE CASCADE,
    FOREIGN KEY(person_id) REFERENCES Person(id)
);

INSERT INTO StrengthGoal
    (goal_id, person_id, e_name, start_reps, start_sets, start_weight, goal_reps, goal_sets, goal_weight)
VALUES
    ('h8w3j9', '16b23a2k4b', 'squat', 10, 3, 30.0, 15, 3, 45.0);

INSERT INTO StrengthGoal
    (goal_id, person_id, e_name, start_reps, start_sets, start_weight, goal_reps, goal_sets, goal_weight)
VALUES
    ('j0c9n4', '16b23a2k4b', 'deadlift', 12, 3, 100.5, 12, 3, 150.0);

INSERT INTO StrengthGoal
    (goal_id, person_id, e_name, start_reps, start_sets, start_weight, goal_reps, goal_sets, goal_weight)
VALUES
    ('l9b1m2', '16b23a2k4b', 'bench press', 10, 3, 10.0, 12, 3, 20.0);

INSERT INTO StrengthGoal
    (goal_id, person_id, e_name, start_reps, start_sets, start_weight, goal_reps, goal_sets, goal_weight)
VALUES
    ('j4k6n1', '16b23a2k4b', 'squat', 10, 3, 20.0, 15, 3, 30.0);

INSERT INTO StrengthGoal
    (goal_id, person_id, e_name, start_reps, start_sets, start_weight, goal_reps, goal_sets, goal_weight)
VALUES
    ('j4k6n2', '16b23a2k4b', 'squat', 10, 3, 45.0, 15, 3, 60.0);

INSERT INTO StrengthGoal
    (goal_id, person_id, e_name, start_reps, start_sets, start_weight, goal_reps, goal_sets, goal_weight)
VALUES
    ('k0e3n8', '16b23a2k4b', 'bench press', 10, 3, 20.0, 12, 3, 30.0);

CREATE TABLE GymSession_Exercise
(
	sets int,
	reps int,
	weight real,
	exercise_name char(35),
	session_id char(5),
	PRIMARY KEY (exercise_name, session_id),
	FOREIGN KEY (exercise_name) REFERENCES Exercise(e_name),
	FOREIGN KEY (session_id) REFERENCES GymSession(session_id)
);

INSERT INTO GymSession_Exercise
    (sets, reps, weight, exercise_name, session_id)
VALUES
    (3, 15, null, 'squat', '1k53g');

INSERT INTO GymSession_Exercise
    (sets, reps, weight, exercise_name, session_id)
VALUES
    (3, 10, 10.5, 'bench press', '1r45s') ;   

INSERT INTO GymSession_Exercise
    (sets, reps, weight, exercise_name, session_id)
VALUES
    (4, 6, 100, 'deadlift', '5h09x') ; 







