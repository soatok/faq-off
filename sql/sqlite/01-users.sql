CREATE TABLE faqoff_user (
    userid INTEGER PRIMARY KEY,
    login TEXT UNIQUE,
    pwhash TEXT, -- encrypted after hashing
    mfasecret TEXT,
    created TEXT,
    modified TEXT
);

CREATE TABLE faqoff_role (
    roleid INTEGER PRIMARY KEY,
    parent INTEGER NULL REFERENCES faqoff_role(roleid),
    name TEXT,
    created TEXT,
    modified TEXT
);

CREATE TABLE faqoff_user_role (
    userid REFERENCES faqoff_user(userid),
    roleid REFERENCES faqoff_role(roleid)
);
