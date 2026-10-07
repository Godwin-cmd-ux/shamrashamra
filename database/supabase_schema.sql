--
-- PostgreSQL database dump
--


-- Dumped from database version 14.24 (Ubuntu 14.24-0ubuntu0.22.04.1)
-- Dumped by pg_dump version 14.24 (Ubuntu 14.24-0ubuntu0.22.04.1)

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: attendance_records; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.attendance_records (
    id bigint NOT NULL,
    event_id bigint NOT NULL,
    invitation_id bigint NOT NULL,
    attendant_id bigint,
    guest_count smallint DEFAULT '1'::smallint NOT NULL,
    method character varying(255) DEFAULT 'qr'::character varying NOT NULL,
    checked_in_at timestamp(0) without time zone NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: attendance_records_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.attendance_records_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: attendance_records_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.attendance_records_id_seq OWNED BY public.attendance_records.id;


--
-- Name: audit_logs; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.audit_logs (
    id bigint NOT NULL,
    event_id bigint,
    actor_id bigint,
    action character varying(255) NOT NULL,
    subject_type character varying(255),
    subject_id character varying(255),
    metadata json,
    ip_hash character varying(64),
    created_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


--
-- Name: audit_logs_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.audit_logs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: audit_logs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.audit_logs_id_seq OWNED BY public.audit_logs.id;


--
-- Name: budget_categories; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.budget_categories (
    id bigint NOT NULL,
    event_id bigint NOT NULL,
    name character varying(255) NOT NULL,
    planned_amount_minor bigint DEFAULT '0'::bigint NOT NULL,
    currency character varying(3) DEFAULT 'TZS'::character varying NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: budget_categories_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.budget_categories_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: budget_categories_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.budget_categories_id_seq OWNED BY public.budget_categories.id;


--
-- Name: cache; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.cache (
    key character varying(255) NOT NULL,
    value text NOT NULL,
    expiration bigint NOT NULL
);


--
-- Name: cache_locks; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.cache_locks (
    key character varying(255) NOT NULL,
    owner character varying(255) NOT NULL,
    expiration bigint NOT NULL
);


--
-- Name: contributions; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.contributions (
    id bigint NOT NULL,
    event_id bigint NOT NULL,
    pledge_id bigint,
    amount_minor bigint NOT NULL,
    currency character varying(3) DEFAULT 'TZS'::character varying NOT NULL,
    received_at date NOT NULL,
    method character varying(255) DEFAULT 'cash'::character varying NOT NULL,
    reference character varying(255),
    note character varying(255),
    recorded_by bigint,
    reverses_id bigint,
    reversed_at timestamp(0) without time zone,
    reversed_by bigint,
    reversal_reason character varying(255),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: contributions_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.contributions_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: contributions_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.contributions_id_seq OWNED BY public.contributions.id;


--
-- Name: event_categories; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.event_categories (
    id bigint NOT NULL,
    slug character varying(255) NOT NULL,
    name_en character varying(255) NOT NULL,
    name_sw character varying(255) NOT NULL,
    sort_order integer DEFAULT 0 NOT NULL,
    is_active boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: event_categories_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.event_categories_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: event_categories_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.event_categories_id_seq OWNED BY public.event_categories.id;


--
-- Name: event_members; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.event_members (
    id bigint NOT NULL,
    event_id bigint NOT NULL,
    user_id bigint NOT NULL,
    role character varying(255) DEFAULT 'committee'::character varying NOT NULL,
    status character varying(255) DEFAULT 'active'::character varying NOT NULL,
    invited_by bigint,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: event_members_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.event_members_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: event_members_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.event_members_id_seq OWNED BY public.event_members.id;


--
-- Name: event_permissions; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.event_permissions (
    id bigint NOT NULL,
    event_member_id bigint NOT NULL,
    permission character varying(255) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: event_permissions_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.event_permissions_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: event_permissions_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.event_permissions_id_seq OWNED BY public.event_permissions.id;


--
-- Name: events; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.events (
    id bigint NOT NULL,
    public_id uuid NOT NULL,
    organizer_id bigint NOT NULL,
    category_id bigint,
    title character varying(255) NOT NULL,
    status character varying(255) DEFAULT 'draft'::character varying NOT NULL,
    starts_at timestamp(0) without time zone,
    timezone character varying(64) DEFAULT 'Africa/Dar_es_Salaam'::character varying NOT NULL,
    venue_name character varying(255),
    venue_address character varying(255),
    map_link character varying(255),
    description text,
    host_names character varying(255),
    dress_code character varying(255),
    image_path character varying(255),
    rsvp_deadline timestamp(0) without time zone,
    guest_capacity integer,
    settings json,
    published_at timestamp(0) without time zone,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: events_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.events_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: events_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.events_id_seq OWNED BY public.events.id;


--
-- Name: expenses; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.expenses (
    id bigint NOT NULL,
    event_id bigint NOT NULL,
    budget_category_id bigint,
    vendor_id bigint,
    description character varying(255) NOT NULL,
    amount_minor bigint NOT NULL,
    currency character varying(3) DEFAULT 'TZS'::character varying NOT NULL,
    incurred_at date NOT NULL,
    payment_status character varying(255) DEFAULT 'pending'::character varying NOT NULL,
    approval_status character varying(255) DEFAULT 'approved'::character varying NOT NULL,
    approved_by bigint,
    receipt_path character varying(255),
    recorded_by bigint,
    reverses_id bigint,
    reversed_at timestamp(0) without time zone,
    reversed_by bigint,
    reversal_reason character varying(255),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: expenses_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.expenses_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: expenses_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.expenses_id_seq OWNED BY public.expenses.id;


--
-- Name: failed_jobs; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.failed_jobs (
    id bigint NOT NULL,
    uuid character varying(255) NOT NULL,
    connection character varying(255) NOT NULL,
    queue character varying(255) NOT NULL,
    payload text NOT NULL,
    exception text NOT NULL,
    failed_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


--
-- Name: failed_jobs_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.failed_jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: failed_jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.failed_jobs_id_seq OWNED BY public.failed_jobs.id;


--
-- Name: guests; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.guests (
    id bigint NOT NULL,
    event_id bigint NOT NULL,
    name character varying(255) NOT NULL,
    phone character varying(32),
    email character varying(255),
    category character varying(255),
    notes text,
    created_by bigint,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: guests_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.guests_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: guests_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.guests_id_seq OWNED BY public.guests.id;


--
-- Name: invitation_templates; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.invitation_templates (
    id bigint NOT NULL,
    scope character varying(255) DEFAULT 'platform'::character varying NOT NULL,
    event_id bigint,
    category character varying(255),
    name character varying(255) NOT NULL,
    style character varying(255) NOT NULL,
    config json,
    is_active boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: invitation_templates_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.invitation_templates_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: invitation_templates_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.invitation_templates_id_seq OWNED BY public.invitation_templates.id;


--
-- Name: invitations; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.invitations (
    id bigint NOT NULL,
    public_id uuid NOT NULL,
    event_id bigint NOT NULL,
    guest_id bigint,
    label character varying(255),
    entitlement_count smallint DEFAULT '1'::smallint NOT NULL,
    status character varying(255) DEFAULT 'issued'::character varying NOT NULL,
    token_hash character varying(64) NOT NULL,
    token_encrypted text,
    issued_by bigint,
    revoked_at timestamp(0) without time zone,
    revoked_by bigint,
    replaced_by_id bigint,
    rsvp_status character varying(255) DEFAULT 'pending'::character varying NOT NULL,
    rsvp_guest_count smallint,
    rsvp_responded_at timestamp(0) without time zone,
    rsvp_note character varying(255),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: invitations_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.invitations_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: invitations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.invitations_id_seq OWNED BY public.invitations.id;


--
-- Name: job_batches; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.job_batches (
    id character varying(255) NOT NULL,
    name character varying(255) NOT NULL,
    total_jobs integer NOT NULL,
    pending_jobs integer NOT NULL,
    failed_jobs integer NOT NULL,
    failed_job_ids text NOT NULL,
    options text,
    cancelled_at integer,
    created_at integer NOT NULL,
    finished_at integer
);


--
-- Name: jobs; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.jobs (
    id bigint NOT NULL,
    queue character varying(255) NOT NULL,
    payload text NOT NULL,
    attempts smallint NOT NULL,
    reserved_at integer,
    available_at integer NOT NULL,
    created_at integer NOT NULL
);


--
-- Name: jobs_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.jobs_id_seq OWNED BY public.jobs.id;


--
-- Name: migrations; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.migrations (
    id integer NOT NULL,
    migration character varying(255) NOT NULL,
    batch integer NOT NULL
);


--
-- Name: migrations_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.migrations_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: migrations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.migrations_id_seq OWNED BY public.migrations.id;


--
-- Name: notification_attempts; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.notification_attempts (
    id bigint NOT NULL,
    notification_id bigint NOT NULL,
    attempt integer DEFAULT 1 NOT NULL,
    status character varying(255) NOT NULL,
    detail text,
    created_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


--
-- Name: notification_attempts_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.notification_attempts_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: notification_attempts_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.notification_attempts_id_seq OWNED BY public.notification_attempts.id;


--
-- Name: notification_outbox; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.notification_outbox (
    id bigint NOT NULL,
    event_id bigint NOT NULL,
    invitation_id bigint,
    channel character varying(255) NOT NULL,
    recipient character varying(255) NOT NULL,
    subject character varying(255),
    status character varying(255) DEFAULT 'pending'::character varying NOT NULL,
    provider character varying(255),
    provider_reference character varying(255),
    error text,
    sent_at timestamp(0) without time zone,
    created_by bigint,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: notification_outbox_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.notification_outbox_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: notification_outbox_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.notification_outbox_id_seq OWNED BY public.notification_outbox.id;


--
-- Name: password_reset_tokens; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.password_reset_tokens (
    email character varying(255) NOT NULL,
    token character varying(255) NOT NULL,
    created_at timestamp(0) without time zone
);


--
-- Name: pledges; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.pledges (
    id bigint NOT NULL,
    event_id bigint NOT NULL,
    contributor_name character varying(255) NOT NULL,
    contributor_contact character varying(255),
    amount_minor bigint NOT NULL,
    currency character varying(3) DEFAULT 'TZS'::character varying NOT NULL,
    due_date date,
    status character varying(255) DEFAULT 'open'::character varying NOT NULL,
    notes character varying(255),
    recorded_by bigint,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: pledges_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.pledges_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: pledges_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.pledges_id_seq OWNED BY public.pledges.id;


--
-- Name: rsvps; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.rsvps (
    id bigint NOT NULL,
    event_id bigint NOT NULL,
    invitation_id bigint NOT NULL,
    status character varying(255) NOT NULL,
    guest_count smallint,
    note character varying(255),
    source character varying(255) DEFAULT 'link'::character varying NOT NULL,
    ip_hash character varying(64),
    responded_at timestamp(0) without time zone NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: rsvps_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.rsvps_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: rsvps_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.rsvps_id_seq OWNED BY public.rsvps.id;


--
-- Name: scan_attempts; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.scan_attempts (
    id bigint NOT NULL,
    event_id bigint NOT NULL,
    invitation_id bigint,
    attendant_id bigint,
    outcome character varying(255) NOT NULL,
    token_hint character varying(12),
    ip_hash character varying(64),
    created_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


--
-- Name: scan_attempts_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.scan_attempts_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: scan_attempts_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.scan_attempts_id_seq OWNED BY public.scan_attempts.id;


--
-- Name: sessions; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.sessions (
    id character varying(255) NOT NULL,
    user_id bigint,
    ip_address character varying(45),
    user_agent text,
    payload text NOT NULL,
    last_activity integer NOT NULL
);


--
-- Name: users; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.users (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    email character varying(255) NOT NULL,
    email_verified_at timestamp(0) without time zone,
    password character varying(255) NOT NULL,
    phone character varying(32),
    locale character varying(5) DEFAULT 'en'::character varying NOT NULL,
    timezone character varying(64) DEFAULT 'Africa/Dar_es_Salaam'::character varying NOT NULL,
    platform_role character varying(255) DEFAULT 'user'::character varying NOT NULL,
    status character varying(255) DEFAULT 'active'::character varying NOT NULL,
    remember_token character varying(100),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: users_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.users_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: users_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.users_id_seq OWNED BY public.users.id;


--
-- Name: vendors; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.vendors (
    id bigint NOT NULL,
    event_id bigint NOT NULL,
    name character varying(255) NOT NULL,
    type character varying(255) DEFAULT 'other'::character varying NOT NULL,
    contact_name character varying(255),
    phone character varying(32),
    email character varying(255),
    services text,
    agreed_amount_minor bigint DEFAULT '0'::bigint NOT NULL,
    deposit_amount_minor bigint DEFAULT '0'::bigint NOT NULL,
    contract_path character varying(255),
    status character varying(255) DEFAULT 'active'::character varying NOT NULL,
    notes text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: vendors_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.vendors_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: vendors_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.vendors_id_seq OWNED BY public.vendors.id;


--
-- Name: attendance_records id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.attendance_records ALTER COLUMN id SET DEFAULT nextval('public.attendance_records_id_seq'::regclass);


--
-- Name: audit_logs id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.audit_logs ALTER COLUMN id SET DEFAULT nextval('public.audit_logs_id_seq'::regclass);


--
-- Name: budget_categories id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.budget_categories ALTER COLUMN id SET DEFAULT nextval('public.budget_categories_id_seq'::regclass);


--
-- Name: contributions id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.contributions ALTER COLUMN id SET DEFAULT nextval('public.contributions_id_seq'::regclass);


--
-- Name: event_categories id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.event_categories ALTER COLUMN id SET DEFAULT nextval('public.event_categories_id_seq'::regclass);


--
-- Name: event_members id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.event_members ALTER COLUMN id SET DEFAULT nextval('public.event_members_id_seq'::regclass);


--
-- Name: event_permissions id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.event_permissions ALTER COLUMN id SET DEFAULT nextval('public.event_permissions_id_seq'::regclass);


--
-- Name: events id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.events ALTER COLUMN id SET DEFAULT nextval('public.events_id_seq'::regclass);


--
-- Name: expenses id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.expenses ALTER COLUMN id SET DEFAULT nextval('public.expenses_id_seq'::regclass);


--
-- Name: failed_jobs id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.failed_jobs ALTER COLUMN id SET DEFAULT nextval('public.failed_jobs_id_seq'::regclass);


--
-- Name: guests id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.guests ALTER COLUMN id SET DEFAULT nextval('public.guests_id_seq'::regclass);


--
-- Name: invitation_templates id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.invitation_templates ALTER COLUMN id SET DEFAULT nextval('public.invitation_templates_id_seq'::regclass);


--
-- Name: invitations id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.invitations ALTER COLUMN id SET DEFAULT nextval('public.invitations_id_seq'::regclass);


--
-- Name: jobs id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.jobs ALTER COLUMN id SET DEFAULT nextval('public.jobs_id_seq'::regclass);


--
-- Name: migrations id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.migrations ALTER COLUMN id SET DEFAULT nextval('public.migrations_id_seq'::regclass);


--
-- Name: notification_attempts id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.notification_attempts ALTER COLUMN id SET DEFAULT nextval('public.notification_attempts_id_seq'::regclass);


--
-- Name: notification_outbox id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.notification_outbox ALTER COLUMN id SET DEFAULT nextval('public.notification_outbox_id_seq'::regclass);


--
-- Name: pledges id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.pledges ALTER COLUMN id SET DEFAULT nextval('public.pledges_id_seq'::regclass);


--
-- Name: rsvps id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.rsvps ALTER COLUMN id SET DEFAULT nextval('public.rsvps_id_seq'::regclass);


--
-- Name: scan_attempts id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.scan_attempts ALTER COLUMN id SET DEFAULT nextval('public.scan_attempts_id_seq'::regclass);


--
-- Name: users id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users ALTER COLUMN id SET DEFAULT nextval('public.users_id_seq'::regclass);


--
-- Name: vendors id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.vendors ALTER COLUMN id SET DEFAULT nextval('public.vendors_id_seq'::regclass);


--
-- Name: attendance_records attendance_records_event_id_invitation_id_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.attendance_records
    ADD CONSTRAINT attendance_records_event_id_invitation_id_unique UNIQUE (event_id, invitation_id);


--
-- Name: attendance_records attendance_records_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.attendance_records
    ADD CONSTRAINT attendance_records_pkey PRIMARY KEY (id);


--
-- Name: audit_logs audit_logs_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.audit_logs
    ADD CONSTRAINT audit_logs_pkey PRIMARY KEY (id);


--
-- Name: budget_categories budget_categories_event_id_name_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.budget_categories
    ADD CONSTRAINT budget_categories_event_id_name_unique UNIQUE (event_id, name);


--
-- Name: budget_categories budget_categories_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.budget_categories
    ADD CONSTRAINT budget_categories_pkey PRIMARY KEY (id);


--
-- Name: cache_locks cache_locks_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cache_locks
    ADD CONSTRAINT cache_locks_pkey PRIMARY KEY (key);


--
-- Name: cache cache_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cache
    ADD CONSTRAINT cache_pkey PRIMARY KEY (key);


--
-- Name: contributions contributions_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.contributions
    ADD CONSTRAINT contributions_pkey PRIMARY KEY (id);


--
-- Name: event_categories event_categories_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.event_categories
    ADD CONSTRAINT event_categories_pkey PRIMARY KEY (id);


--
-- Name: event_categories event_categories_slug_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.event_categories
    ADD CONSTRAINT event_categories_slug_unique UNIQUE (slug);


--
-- Name: event_members event_members_event_id_user_id_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.event_members
    ADD CONSTRAINT event_members_event_id_user_id_unique UNIQUE (event_id, user_id);


--
-- Name: event_members event_members_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.event_members
    ADD CONSTRAINT event_members_pkey PRIMARY KEY (id);


--
-- Name: event_permissions event_permissions_event_member_id_permission_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.event_permissions
    ADD CONSTRAINT event_permissions_event_member_id_permission_unique UNIQUE (event_member_id, permission);


--
-- Name: event_permissions event_permissions_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.event_permissions
    ADD CONSTRAINT event_permissions_pkey PRIMARY KEY (id);


--
-- Name: events events_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.events
    ADD CONSTRAINT events_pkey PRIMARY KEY (id);


--
-- Name: events events_public_id_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.events
    ADD CONSTRAINT events_public_id_unique UNIQUE (public_id);


--
-- Name: expenses expenses_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.expenses
    ADD CONSTRAINT expenses_pkey PRIMARY KEY (id);


--
-- Name: failed_jobs failed_jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_pkey PRIMARY KEY (id);


--
-- Name: failed_jobs failed_jobs_uuid_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_uuid_unique UNIQUE (uuid);


--
-- Name: guests guests_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.guests
    ADD CONSTRAINT guests_pkey PRIMARY KEY (id);


--
-- Name: invitation_templates invitation_templates_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.invitation_templates
    ADD CONSTRAINT invitation_templates_pkey PRIMARY KEY (id);


--
-- Name: invitations invitations_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.invitations
    ADD CONSTRAINT invitations_pkey PRIMARY KEY (id);


--
-- Name: invitations invitations_public_id_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.invitations
    ADD CONSTRAINT invitations_public_id_unique UNIQUE (public_id);


--
-- Name: invitations invitations_token_hash_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.invitations
    ADD CONSTRAINT invitations_token_hash_unique UNIQUE (token_hash);


--
-- Name: job_batches job_batches_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.job_batches
    ADD CONSTRAINT job_batches_pkey PRIMARY KEY (id);


--
-- Name: jobs jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.jobs
    ADD CONSTRAINT jobs_pkey PRIMARY KEY (id);


--
-- Name: migrations migrations_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.migrations
    ADD CONSTRAINT migrations_pkey PRIMARY KEY (id);


--
-- Name: notification_attempts notification_attempts_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.notification_attempts
    ADD CONSTRAINT notification_attempts_pkey PRIMARY KEY (id);


--
-- Name: notification_outbox notification_outbox_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.notification_outbox
    ADD CONSTRAINT notification_outbox_pkey PRIMARY KEY (id);


--
-- Name: password_reset_tokens password_reset_tokens_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.password_reset_tokens
    ADD CONSTRAINT password_reset_tokens_pkey PRIMARY KEY (email);


--
-- Name: pledges pledges_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.pledges
    ADD CONSTRAINT pledges_pkey PRIMARY KEY (id);


--
-- Name: rsvps rsvps_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.rsvps
    ADD CONSTRAINT rsvps_pkey PRIMARY KEY (id);


--
-- Name: scan_attempts scan_attempts_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.scan_attempts
    ADD CONSTRAINT scan_attempts_pkey PRIMARY KEY (id);


--
-- Name: sessions sessions_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.sessions
    ADD CONSTRAINT sessions_pkey PRIMARY KEY (id);


--
-- Name: users users_email_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_email_unique UNIQUE (email);


--
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- Name: vendors vendors_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.vendors
    ADD CONSTRAINT vendors_pkey PRIMARY KEY (id);


--
-- Name: attendance_records_attendant_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX attendance_records_attendant_id_index ON public.attendance_records USING btree (attendant_id);


--
-- Name: audit_logs_actor_id_created_at_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX audit_logs_actor_id_created_at_index ON public.audit_logs USING btree (actor_id, created_at);


--
-- Name: audit_logs_event_id_created_at_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX audit_logs_event_id_created_at_index ON public.audit_logs USING btree (event_id, created_at);


--
-- Name: cache_expiration_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX cache_expiration_index ON public.cache USING btree (expiration);


--
-- Name: cache_locks_expiration_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX cache_locks_expiration_index ON public.cache_locks USING btree (expiration);


--
-- Name: contributions_event_id_received_at_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX contributions_event_id_received_at_index ON public.contributions USING btree (event_id, received_at);


--
-- Name: event_members_user_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX event_members_user_id_index ON public.event_members USING btree (user_id);


--
-- Name: events_organizer_id_status_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX events_organizer_id_status_index ON public.events USING btree (organizer_id, status);


--
-- Name: events_starts_at_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX events_starts_at_index ON public.events USING btree (starts_at);


--
-- Name: events_status_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX events_status_index ON public.events USING btree (status);


--
-- Name: expenses_event_id_incurred_at_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX expenses_event_id_incurred_at_index ON public.expenses USING btree (event_id, incurred_at);


--
-- Name: failed_jobs_connection_queue_failed_at_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX failed_jobs_connection_queue_failed_at_index ON public.failed_jobs USING btree (connection, queue, failed_at);


--
-- Name: guests_event_id_category_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX guests_event_id_category_index ON public.guests USING btree (event_id, category);


--
-- Name: guests_event_id_phone_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX guests_event_id_phone_index ON public.guests USING btree (event_id, phone);


--
-- Name: invitations_event_id_rsvp_status_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX invitations_event_id_rsvp_status_index ON public.invitations USING btree (event_id, rsvp_status);


--
-- Name: invitations_event_id_status_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX invitations_event_id_status_index ON public.invitations USING btree (event_id, status);


--
-- Name: jobs_queue_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX jobs_queue_index ON public.jobs USING btree (queue);


--
-- Name: notification_attempts_notification_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX notification_attempts_notification_id_index ON public.notification_attempts USING btree (notification_id);


--
-- Name: notification_outbox_event_id_channel_status_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX notification_outbox_event_id_channel_status_index ON public.notification_outbox USING btree (event_id, channel, status);


--
-- Name: pledges_event_id_status_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX pledges_event_id_status_index ON public.pledges USING btree (event_id, status);


--
-- Name: rsvps_invitation_id_responded_at_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX rsvps_invitation_id_responded_at_index ON public.rsvps USING btree (invitation_id, responded_at);


--
-- Name: scan_attempts_event_id_created_at_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX scan_attempts_event_id_created_at_index ON public.scan_attempts USING btree (event_id, created_at);


--
-- Name: sessions_last_activity_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX sessions_last_activity_index ON public.sessions USING btree (last_activity);


--
-- Name: sessions_user_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX sessions_user_id_index ON public.sessions USING btree (user_id);


--
-- Name: vendors_event_id_type_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX vendors_event_id_type_index ON public.vendors USING btree (event_id, type);


--
-- Name: attendance_records attendance_records_attendant_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.attendance_records
    ADD CONSTRAINT attendance_records_attendant_id_foreign FOREIGN KEY (attendant_id) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: attendance_records attendance_records_event_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.attendance_records
    ADD CONSTRAINT attendance_records_event_id_foreign FOREIGN KEY (event_id) REFERENCES public.events(id) ON DELETE CASCADE;


--
-- Name: attendance_records attendance_records_invitation_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.attendance_records
    ADD CONSTRAINT attendance_records_invitation_id_foreign FOREIGN KEY (invitation_id) REFERENCES public.invitations(id) ON DELETE CASCADE;


--
-- Name: audit_logs audit_logs_actor_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.audit_logs
    ADD CONSTRAINT audit_logs_actor_id_foreign FOREIGN KEY (actor_id) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: audit_logs audit_logs_event_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.audit_logs
    ADD CONSTRAINT audit_logs_event_id_foreign FOREIGN KEY (event_id) REFERENCES public.events(id) ON DELETE CASCADE;


--
-- Name: budget_categories budget_categories_event_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.budget_categories
    ADD CONSTRAINT budget_categories_event_id_foreign FOREIGN KEY (event_id) REFERENCES public.events(id) ON DELETE CASCADE;


--
-- Name: contributions contributions_event_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.contributions
    ADD CONSTRAINT contributions_event_id_foreign FOREIGN KEY (event_id) REFERENCES public.events(id) ON DELETE CASCADE;


--
-- Name: contributions contributions_pledge_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.contributions
    ADD CONSTRAINT contributions_pledge_id_foreign FOREIGN KEY (pledge_id) REFERENCES public.pledges(id) ON DELETE SET NULL;


--
-- Name: contributions contributions_recorded_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.contributions
    ADD CONSTRAINT contributions_recorded_by_foreign FOREIGN KEY (recorded_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: contributions contributions_reversed_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.contributions
    ADD CONSTRAINT contributions_reversed_by_foreign FOREIGN KEY (reversed_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: contributions contributions_reverses_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.contributions
    ADD CONSTRAINT contributions_reverses_id_foreign FOREIGN KEY (reverses_id) REFERENCES public.contributions(id) ON DELETE SET NULL;


--
-- Name: event_members event_members_event_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.event_members
    ADD CONSTRAINT event_members_event_id_foreign FOREIGN KEY (event_id) REFERENCES public.events(id) ON DELETE CASCADE;


--
-- Name: event_members event_members_invited_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.event_members
    ADD CONSTRAINT event_members_invited_by_foreign FOREIGN KEY (invited_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: event_members event_members_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.event_members
    ADD CONSTRAINT event_members_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: event_permissions event_permissions_event_member_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.event_permissions
    ADD CONSTRAINT event_permissions_event_member_id_foreign FOREIGN KEY (event_member_id) REFERENCES public.event_members(id) ON DELETE CASCADE;


--
-- Name: events events_category_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.events
    ADD CONSTRAINT events_category_id_foreign FOREIGN KEY (category_id) REFERENCES public.event_categories(id) ON DELETE SET NULL;


--
-- Name: events events_organizer_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.events
    ADD CONSTRAINT events_organizer_id_foreign FOREIGN KEY (organizer_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: expenses expenses_approved_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.expenses
    ADD CONSTRAINT expenses_approved_by_foreign FOREIGN KEY (approved_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: expenses expenses_budget_category_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.expenses
    ADD CONSTRAINT expenses_budget_category_id_foreign FOREIGN KEY (budget_category_id) REFERENCES public.budget_categories(id) ON DELETE SET NULL;


--
-- Name: expenses expenses_event_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.expenses
    ADD CONSTRAINT expenses_event_id_foreign FOREIGN KEY (event_id) REFERENCES public.events(id) ON DELETE CASCADE;


--
-- Name: expenses expenses_recorded_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.expenses
    ADD CONSTRAINT expenses_recorded_by_foreign FOREIGN KEY (recorded_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: expenses expenses_reversed_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.expenses
    ADD CONSTRAINT expenses_reversed_by_foreign FOREIGN KEY (reversed_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: expenses expenses_reverses_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.expenses
    ADD CONSTRAINT expenses_reverses_id_foreign FOREIGN KEY (reverses_id) REFERENCES public.expenses(id) ON DELETE SET NULL;


--
-- Name: expenses expenses_vendor_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.expenses
    ADD CONSTRAINT expenses_vendor_id_foreign FOREIGN KEY (vendor_id) REFERENCES public.vendors(id) ON DELETE SET NULL;


--
-- Name: guests guests_created_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.guests
    ADD CONSTRAINT guests_created_by_foreign FOREIGN KEY (created_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: guests guests_event_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.guests
    ADD CONSTRAINT guests_event_id_foreign FOREIGN KEY (event_id) REFERENCES public.events(id) ON DELETE CASCADE;


--
-- Name: invitation_templates invitation_templates_event_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.invitation_templates
    ADD CONSTRAINT invitation_templates_event_id_foreign FOREIGN KEY (event_id) REFERENCES public.events(id) ON DELETE CASCADE;


--
-- Name: invitations invitations_event_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.invitations
    ADD CONSTRAINT invitations_event_id_foreign FOREIGN KEY (event_id) REFERENCES public.events(id) ON DELETE CASCADE;


--
-- Name: invitations invitations_guest_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.invitations
    ADD CONSTRAINT invitations_guest_id_foreign FOREIGN KEY (guest_id) REFERENCES public.guests(id) ON DELETE SET NULL;


--
-- Name: invitations invitations_issued_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.invitations
    ADD CONSTRAINT invitations_issued_by_foreign FOREIGN KEY (issued_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: invitations invitations_replaced_by_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.invitations
    ADD CONSTRAINT invitations_replaced_by_id_foreign FOREIGN KEY (replaced_by_id) REFERENCES public.invitations(id) ON DELETE SET NULL;


--
-- Name: invitations invitations_revoked_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.invitations
    ADD CONSTRAINT invitations_revoked_by_foreign FOREIGN KEY (revoked_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: notification_attempts notification_attempts_notification_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.notification_attempts
    ADD CONSTRAINT notification_attempts_notification_id_foreign FOREIGN KEY (notification_id) REFERENCES public.notification_outbox(id) ON DELETE CASCADE;


--
-- Name: notification_outbox notification_outbox_created_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.notification_outbox
    ADD CONSTRAINT notification_outbox_created_by_foreign FOREIGN KEY (created_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: notification_outbox notification_outbox_event_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.notification_outbox
    ADD CONSTRAINT notification_outbox_event_id_foreign FOREIGN KEY (event_id) REFERENCES public.events(id) ON DELETE CASCADE;


--
-- Name: notification_outbox notification_outbox_invitation_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.notification_outbox
    ADD CONSTRAINT notification_outbox_invitation_id_foreign FOREIGN KEY (invitation_id) REFERENCES public.invitations(id) ON DELETE SET NULL;


--
-- Name: pledges pledges_event_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.pledges
    ADD CONSTRAINT pledges_event_id_foreign FOREIGN KEY (event_id) REFERENCES public.events(id) ON DELETE CASCADE;


--
-- Name: pledges pledges_recorded_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.pledges
    ADD CONSTRAINT pledges_recorded_by_foreign FOREIGN KEY (recorded_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: rsvps rsvps_event_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.rsvps
    ADD CONSTRAINT rsvps_event_id_foreign FOREIGN KEY (event_id) REFERENCES public.events(id) ON DELETE CASCADE;


--
-- Name: rsvps rsvps_invitation_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.rsvps
    ADD CONSTRAINT rsvps_invitation_id_foreign FOREIGN KEY (invitation_id) REFERENCES public.invitations(id) ON DELETE CASCADE;


--
-- Name: scan_attempts scan_attempts_attendant_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.scan_attempts
    ADD CONSTRAINT scan_attempts_attendant_id_foreign FOREIGN KEY (attendant_id) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: scan_attempts scan_attempts_event_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.scan_attempts
    ADD CONSTRAINT scan_attempts_event_id_foreign FOREIGN KEY (event_id) REFERENCES public.events(id) ON DELETE CASCADE;


--
-- Name: scan_attempts scan_attempts_invitation_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.scan_attempts
    ADD CONSTRAINT scan_attempts_invitation_id_foreign FOREIGN KEY (invitation_id) REFERENCES public.invitations(id) ON DELETE SET NULL;


--
-- Name: vendors vendors_event_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.vendors
    ADD CONSTRAINT vendors_event_id_foreign FOREIGN KEY (event_id) REFERENCES public.events(id) ON DELETE CASCADE;


--
-- PostgreSQL database dump complete
--


--
-- PostgreSQL database dump
--


-- Dumped from database version 14.24 (Ubuntu 14.24-0ubuntu0.22.04.1)
-- Dumped by pg_dump version 14.24 (Ubuntu 14.24-0ubuntu0.22.04.1)

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- Data for Name: event_categories; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.event_categories VALUES (1, 'wedding', 'Wedding', 'Harusi', 1, true, '2026-10-05 04:32:47', '2026-10-05 04:32:47');
INSERT INTO public.event_categories VALUES (2, 'sendoff', 'Sendoff', 'Kuaga', 2, true, '2026-10-05 04:32:47', '2026-10-05 04:32:47');
INSERT INTO public.event_categories VALUES (3, 'kitchen_party', 'Kitchen Party', 'Kitchen Party', 3, true, '2026-10-05 04:32:47', '2026-10-05 04:32:47');
INSERT INTO public.event_categories VALUES (4, 'birthday', 'Birthday', 'Siku ya Kuzaliwa', 4, true, '2026-10-05 04:32:47', '2026-10-05 04:32:47');
INSERT INTO public.event_categories VALUES (5, 'graduation', 'Graduation', 'Kuhitimu', 5, true, '2026-10-05 04:32:47', '2026-10-05 04:32:47');
INSERT INTO public.event_categories VALUES (6, 'anniversary', 'Anniversary', 'Kumbukumbu', 6, true, '2026-10-05 04:32:47', '2026-10-05 04:32:47');
INSERT INTO public.event_categories VALUES (7, 'engagement', 'Engagement', 'Uguano', 7, true, '2026-10-05 04:32:47', '2026-10-05 04:32:47');
INSERT INTO public.event_categories VALUES (8, 'corporate', 'Corporate Event', 'Tukio la Kampuni', 8, true, '2026-10-05 04:32:47', '2026-10-05 04:32:47');
INSERT INTO public.event_categories VALUES (9, 'other', 'Other Celebration', 'Sherehe Nyingine', 9, true, '2026-10-05 04:32:47', '2026-10-05 04:32:47');


--
-- Data for Name: invitation_templates; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.invitation_templates VALUES (1, 'platform', NULL, NULL, 'Elegant Luxury', 'elegant', '{"background":"#12261f","surface":"#1b3a30","text":"#f5efe2","muted":"#c9bfa8","accent":"#d4af37","heading_font":"Playfair Display, Georgia, serif","body_font":"Instrument Sans, system-ui, sans-serif","ornament":"diamond"}', true, '2026-10-05 04:33:11', '2026-10-05 04:33:11');
INSERT INTO public.invitation_templates VALUES (2, 'platform', NULL, NULL, 'Minimalist', 'minimalist', '{"background":"#ffffff","surface":"#fafafa","text":"#18181b","muted":"#71717a","accent":"#18181b","heading_font":"Instrument Sans, system-ui, sans-serif","body_font":"Instrument Sans, system-ui, sans-serif","ornament":"line"}', true, '2026-10-05 04:33:11', '2026-10-05 04:33:11');
INSERT INTO public.invitation_templates VALUES (3, 'platform', NULL, NULL, 'Floral', 'floral', '{"background":"#fdf7f2","surface":"#ffffff","text":"#4a2f28","muted":"#9c7a6d","accent":"#c25e6b","heading_font":"Playfair Display, Georgia, serif","body_font":"Instrument Sans, system-ui, sans-serif","ornament":"floral"}', true, '2026-10-05 04:33:11', '2026-10-05 04:33:11');
INSERT INTO public.invitation_templates VALUES (4, 'platform', NULL, NULL, 'Traditional African', 'traditional', '{"background":"#fbf3e4","surface":"#fffaf0","text":"#3e2a12","muted":"#8a6d3b","accent":"#b4541e","heading_font":"Playfair Display, Georgia, serif","body_font":"Instrument Sans, system-ui, sans-serif","ornament":"kente"}', true, '2026-10-05 04:33:11', '2026-10-05 04:33:11');
INSERT INTO public.invitation_templates VALUES (5, 'platform', NULL, NULL, 'Modern', 'modern', '{"background":"#0b1220","surface":"#131c2e","text":"#eef2ff","muted":"#94a3b8","accent":"#38bdf8","heading_font":"Instrument Sans, system-ui, sans-serif","body_font":"Instrument Sans, system-ui, sans-serif","ornament":"geometric"}', true, '2026-10-05 04:33:11', '2026-10-05 04:33:11');
INSERT INTO public.invitation_templates VALUES (6, 'platform', NULL, NULL, 'Formal Corporate', 'corporate', '{"background":"#ffffff","surface":"#f4f6f8","text":"#1f2937","muted":"#6b7280","accent":"#1e3a5f","heading_font":"Instrument Sans, system-ui, sans-serif","body_font":"Instrument Sans, system-ui, sans-serif","ornament":"line"}', true, '2026-10-05 04:33:11', '2026-10-05 04:33:11');
INSERT INTO public.invitation_templates VALUES (7, 'platform', NULL, NULL, 'Colorful Celebration', 'colorful', '{"background":"#fff8ec","surface":"#ffffff","text":"#3b2a52","muted":"#8b7aa8","accent":"#f59e0b","heading_font":"Playfair Display, Georgia, serif","body_font":"Instrument Sans, system-ui, sans-serif","ornament":"confetti"}', true, '2026-10-05 04:33:11', '2026-10-05 04:33:11');


--
-- Data for Name: migrations; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.migrations VALUES (1, '0001_01_01_000000_create_users_table', 1);
INSERT INTO public.migrations VALUES (2, '0001_01_01_000001_create_cache_table', 1);
INSERT INTO public.migrations VALUES (3, '0001_01_01_000002_create_jobs_table', 1);
INSERT INTO public.migrations VALUES (4, '2026_10_05_000001_create_event_categories_table', 1);
INSERT INTO public.migrations VALUES (5, '2026_10_05_000002_create_events_table', 1);
INSERT INTO public.migrations VALUES (6, '2026_10_05_000003_create_event_members_table', 1);
INSERT INTO public.migrations VALUES (7, '2026_10_05_000004_create_invitation_templates_table', 1);
INSERT INTO public.migrations VALUES (8, '2026_10_05_000005_create_guests_table', 1);
INSERT INTO public.migrations VALUES (9, '2026_10_05_000006_create_invitations_table', 1);
INSERT INTO public.migrations VALUES (10, '2026_10_05_000007_create_rsvps_table', 1);
INSERT INTO public.migrations VALUES (11, '2026_10_05_000008_create_attendance_tables', 1);
INSERT INTO public.migrations VALUES (12, '2026_10_05_000009_create_vendors_table', 1);
INSERT INTO public.migrations VALUES (13, '2026_10_05_000010_create_finance_tables', 1);
INSERT INTO public.migrations VALUES (14, '2026_10_05_000011_create_notifications_tables', 1);
INSERT INTO public.migrations VALUES (15, '2026_10_05_000012_create_audit_logs_table', 1);


--
-- Name: event_categories_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.event_categories_id_seq', 9, true);


--
-- Name: invitation_templates_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.invitation_templates_id_seq', 7, true);


--
-- Name: migrations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.migrations_id_seq', 15, true);


--
-- PostgreSQL database dump complete
--


