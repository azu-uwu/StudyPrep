-- =========================================================
-- 1. Users
-- =========================================================

CREATE TABLE IF NOT EXISTS Users (
    user_id BIGINT AUTO_INCREMENT PRIMARY KEY,
    UserName VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    avatar_url VARCHAR(500) NULL,
    role VARCHAR(20) NOT NULL DEFAULT 'user',
    energy INT NOT NULL DEFAULT 5,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    failed_login_attempts INT NOT NULL DEFAULT 0,
    locked_until DATETIME NULL,
    email_verified_at DATETIME NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'ACTIVE'
) ENGINE=InnoDB;


-- =========================================================
-- 2. Skills
-- =========================================================

CREATE TABLE IF NOT EXISTS Skills (
    skill_id TINYINT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(20) NOT NULL UNIQUE,
    skill_name VARCHAR(50) NOT NULL
) ENGINE=InnoDB;


-- =========================================================
-- 3. Goals
-- =========================================================

CREATE TABLE IF NOT EXISTS Goals (
    goal_id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT NOT NULL,
    certificate VARCHAR(100) NULL,
    target_listening SMALLINT NULL,
    target_reading SMALLINT NULL,
    target_speaking SMALLINT NULL,
    target_writing SMALLINT NULL,
    target_date DATE NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT FK_Goals_Users
        FOREIGN KEY (user_id)
        REFERENCES Users(user_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB;


-- =========================================================
-- 4. ExamSections
-- =========================================================

CREATE TABLE IF NOT EXISTS ExamSections (
    section_id BIGINT AUTO_INCREMENT PRIMARY KEY,
    skill_id TINYINT NOT NULL,
    code VARCHAR(20) NOT NULL,
    name VARCHAR(150) NOT NULL,
    sequence_no SMALLINT NOT NULL,
    default_time_limit INT NULL,

    CONSTRAINT FK_ExamSections_Skills
        FOREIGN KEY (skill_id)
        REFERENCES Skills(skill_id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
) ENGINE=InnoDB;


-- =========================================================
-- 5. QuestionTypes
-- =========================================================

CREATE TABLE IF NOT EXISTS QuestionTypes (
    question_type_id BIGINT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    response_mode VARCHAR(20) NOT NULL
) ENGINE=InnoDB;


-- =========================================================
-- 6. QuestionSets
-- =========================================================

CREATE TABLE IF NOT EXISTS QuestionSets (
    question_set_id BIGINT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    stimulus_text TEXT NULL,
    media_url VARCHAR(500) NULL,
    media_type VARCHAR(20) NULL,
    transcript TEXT NULL
) ENGINE=InnoDB;


-- =========================================================
-- 7. Questions
-- =========================================================

CREATE TABLE IF NOT EXISTS Questions (
    question_id BIGINT AUTO_INCREMENT PRIMARY KEY,
    section_id BIGINT NOT NULL,
    question_type_id BIGINT NOT NULL,
    question_set_id BIGINT NULL,
    content TEXT NOT NULL,
    explanation TEXT NULL,
    difficulty VARCHAR(20) NOT NULL,
    points DECIMAL(6,2) NOT NULL DEFAULT 1.00,
    status VARCHAR(20) NOT NULL DEFAULT 'DRAFT',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT FK_Questions_ExamSections
        FOREIGN KEY (section_id)
        REFERENCES ExamSections(section_id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,

    CONSTRAINT FK_Questions_QuestionTypes
        FOREIGN KEY (question_type_id)
        REFERENCES QuestionTypes(question_type_id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,

    CONSTRAINT FK_Questions_QuestionSets
        FOREIGN KEY (question_set_id)
        REFERENCES QuestionSets(question_set_id)
        ON DELETE SET NULL
        ON UPDATE CASCADE
) ENGINE=InnoDB;


-- =========================================================
-- 8. AnswerOptions
-- =========================================================

CREATE TABLE IF NOT EXISTS AnswerOptions (
    option_id BIGINT AUTO_INCREMENT PRIMARY KEY,
    question_id BIGINT NOT NULL,
    label VARCHAR(5) NOT NULL,
    content TEXT NOT NULL,
    is_correct BOOLEAN NOT NULL DEFAULT FALSE,

    CONSTRAINT FK_AnswerOptions_Questions
        FOREIGN KEY (question_id)
        REFERENCES Questions(question_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT UQ_AnswerOptions
        UNIQUE (question_id, label)
) ENGINE=InnoDB;


-- =========================================================
-- 9. Topics
-- =========================================================

CREATE TABLE IF NOT EXISTS Topics (
    topic_id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT NULL,
    parent_topic_id BIGINT NULL,

    CONSTRAINT FK_Topics_Users
        FOREIGN KEY (user_id)
        REFERENCES Users(user_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT FK_Topics_Parent
        FOREIGN KEY (parent_topic_id)
        REFERENCES Topics(topic_id)
        ON DELETE SET NULL
        ON UPDATE CASCADE
) ENGINE=InnoDB;


-- =========================================================
-- 10. Vocabularies
-- =========================================================

CREATE TABLE IF NOT EXISTS Vocabularies (
    vocabulary_id BIGINT AUTO_INCREMENT PRIMARY KEY,
    topic_id BIGINT NOT NULL,
    word VARCHAR(150) NOT NULL,
    meaning_vi TEXT NOT NULL,
    part_of_speech VARCHAR(30) NULL,
    pronunciation VARCHAR(150) NULL,
    example_en TEXT NULL,
    example_vi TEXT NULL,

    CONSTRAINT FK_Vocabularies_Topics
        FOREIGN KEY (topic_id)
        REFERENCES Topics(topic_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB;


-- =========================================================
-- 11. UserVocabularies
-- =========================================================

CREATE TABLE IF NOT EXISTS UserVocabularies (
    user_voca_id BIGINT AUTO_INCREMENT PRIMARY KEY,
    vocabulary_id BIGINT NOT NULL,
    user_id BIGINT NOT NULL,
    learned_at DATETIME NULL,
    is_done BOOLEAN NOT NULL DEFAULT FALSE,

    CONSTRAINT FK_UserVocabularies_Vocabularies
        FOREIGN KEY (vocabulary_id)
        REFERENCES Vocabularies(vocabulary_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT FK_UserVocabularies_Users
        FOREIGN KEY (user_id)
        REFERENCES Users(user_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT UQ_UserVocabularies
        UNIQUE (user_id, vocabulary_id)
) ENGINE=InnoDB;


-- =========================================================
-- 12. TestBlueprints
-- =========================================================

CREATE TABLE IF NOT EXISTS TestBlueprints (
    blueprint_id BIGINT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    exam_type VARCHAR(20) NOT NULL,
    version VARCHAR(30) NULL,
    total_time INT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'DRAFT'
) ENGINE=InnoDB;


-- =========================================================
-- 13. BlueprintRules
-- =========================================================

CREATE TABLE IF NOT EXISTS BlueprintRules (
    rule_id BIGINT AUTO_INCREMENT PRIMARY KEY,
    blueprint_id BIGINT NOT NULL,
    section_id BIGINT NOT NULL,
    question_type_id BIGINT NULL,
    difficulty VARCHAR(20) NULL,
    target_count INT NOT NULL,
    sequence_no SMALLINT NOT NULL,

    CONSTRAINT FK_BlueprintRules_TestBlueprints
        FOREIGN KEY (blueprint_id)
        REFERENCES TestBlueprints(blueprint_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT FK_BlueprintRules_ExamSections
        FOREIGN KEY (section_id)
        REFERENCES ExamSections(section_id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,

    CONSTRAINT FK_BlueprintRules_QuestionTypes
        FOREIGN KEY (question_type_id)
        REFERENCES QuestionTypes(question_type_id)
        ON DELETE SET NULL
        ON UPDATE CASCADE
) ENGINE=InnoDB;


-- =========================================================
-- 14. TestForms
-- =========================================================

CREATE TABLE IF NOT EXISTS TestForms (
    test_form_id BIGINT AUTO_INCREMENT PRIMARY KEY,
    blueprint_id BIGINT NOT NULL,
    title VARCHAR(200) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    generation_seed VARCHAR(100) NULL,

    CONSTRAINT FK_TestForms_TestBlueprints
        FOREIGN KEY (blueprint_id)
        REFERENCES TestBlueprints(blueprint_id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
) ENGINE=InnoDB;


-- =========================================================
-- 15. TestFormQuestions
-- =========================================================

CREATE TABLE IF NOT EXISTS TestFormQuestions (
    test_form_id BIGINT NOT NULL,
    question_id BIGINT NOT NULL,
    sequence_no INT NOT NULL,
    point DECIMAL(6,2) NOT NULL DEFAULT 1.00,

    PRIMARY KEY (test_form_id, question_id),

    CONSTRAINT FK_TestFormQuestions_TestForms
        FOREIGN KEY (test_form_id)
        REFERENCES TestForms(test_form_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT FK_TestFormQuestions_Questions
        FOREIGN KEY (question_id)
        REFERENCES Questions(question_id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
) ENGINE=InnoDB;


-- =========================================================
-- 16. Attempts
-- =========================================================

CREATE TABLE IF NOT EXISTS Attempts (
    attempt_id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT NOT NULL,
    test_form_id BIGINT NOT NULL,
    session_type VARCHAR(20) NOT NULL,
    started_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ended_at DATETIME NULL,
    submitted_at DATETIME NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'IN_PROGRESS',

    CONSTRAINT FK_Attempts_Users
        FOREIGN KEY (user_id)
        REFERENCES Users(user_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT FK_Attempts_TestForms
        FOREIGN KEY (test_form_id)
        REFERENCES TestForms(test_form_id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
) ENGINE=InnoDB;


-- =========================================================
-- 17. AttemptResponses
-- =========================================================

CREATE TABLE IF NOT EXISTS AttemptResponses (
    response_id BIGINT AUTO_INCREMENT PRIMARY KEY,
    attempt_id BIGINT NOT NULL,
    question_id BIGINT NOT NULL,
    text_response TEXT NULL,
    media_url VARCHAR(500) NULL,
    score DECIMAL(6,2) NULL,
    feedback TEXT NULL,
    submitted_at DATETIME NULL,

    CONSTRAINT FK_AttemptResponses_Attempts
        FOREIGN KEY (attempt_id)
        REFERENCES Attempts(attempt_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT FK_AttemptResponses_Questions
        FOREIGN KEY (question_id)
        REFERENCES Questions(question_id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,

    CONSTRAINT UQ_AttemptResponses
        UNIQUE (attempt_id, question_id)
) ENGINE=InnoDB;


-- =========================================================
-- 18. ScoreResults
-- =========================================================

CREATE TABLE IF NOT EXISTS ScoreResults (
    result_id BIGINT AUTO_INCREMENT PRIMARY KEY,
    attempt_id BIGINT NOT NULL UNIQUE,
    listening_score DECIMAL(6,2) NULL,
    reading_score DECIMAL(6,2) NULL,
    speaking_score DECIMAL(6,2) NULL,
    writing_score DECIMAL(6,2) NULL,
    total_score DECIMAL(7,2) NULL,
    graded_at DATETIME NULL,

    CONSTRAINT FK_ScoreResults_Attempts
        FOREIGN KEY (attempt_id)
        REFERENCES Attempts(attempt_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB;


-- =========================================================
-- 19. AIAnalyses
-- =========================================================

CREATE TABLE IF NOT EXISTS AIAnalyses (
    analysis_id BIGINT AUTO_INCREMENT PRIMARY KEY,
    attempt_id BIGINT NOT NULL,
    skill_id TINYINT NULL,
    strengths JSON NULL,
    weaknesses JSON NULL,
    recommendations JSON NULL,
    model_name VARCHAR(100) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT FK_AIAnalyses_Attempts
        FOREIGN KEY (attempt_id)
        REFERENCES Attempts(attempt_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT FK_AIAnalyses_Skills
        FOREIGN KEY (skill_id)
        REFERENCES Skills(skill_id)
        ON DELETE SET NULL
        ON UPDATE CASCADE
) ENGINE=InnoDB;


--- đổi tên -----------
RENAME TABLE
    aianalyses          TO ai_analysis,
    answeroptions       TO answer_option,
    attemptresponses    TO attempt_response,
    blueprintrules      TO blueprint_rule,
    examsections        TO exam_section,
    questionsets        TO question_set,
    questiontypes       TO question_type,
    scoreresults        TO score_result,
    testblueprints      TO test_blueprint,
    testformquestions   TO test_form_question,
    testforms           TO test_form,
    uservocabularies    TO user_vocabulary;