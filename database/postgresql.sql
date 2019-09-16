--
-- PostgreSQL database dump
--

-- Dumped from database version 10.6 (Ubuntu 10.6-0ubuntu0.18.04.1)
-- Dumped by pg_dump version 10.6 (Ubuntu 10.6-0ubuntu0.18.04.1)

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET client_min_messages = warning;
SET row_security = off;

--
-- Name: plpgsql; Type: EXTENSION; Schema: -; Owner: 
--

CREATE EXTENSION IF NOT EXISTS plpgsql WITH SCHEMA pg_catalog;


--
-- Name: EXTENSION plpgsql; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION plpgsql IS 'PL/pgSQL procedural language';


SET default_tablespace = '';

SET default_with_oids = false;

--
-- Name: has_products; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.has_products (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    quantity bigint NOT NULL
);


ALTER TABLE public.has_products OWNER TO postgres;
ALTER TABLE public.has_products REPLICA IDENTITY FULL;

--
-- Name: order; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public."order" (
    id bigint NOT NULL,
    "table" character varying(255) NOT NULL,
    done boolean DEFAULT false NOT NULL,
    pay boolean DEFAULT false NOT NULL
);


ALTER TABLE public."order" OWNER TO postgres;
ALTER TABLE public."order" REPLICA IDENTITY FULL;

--
-- Name: product; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.product (
    name character varying(255) NOT NULL
);


ALTER TABLE public.product OWNER TO postgres;
ALTER TABLE public.product REPLICA IDENTITY FULL;

--
-- Data for Name: has_products; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.has_products (id, name, quantity) FROM stdin;
\.


--
-- Data for Name: order; Type: TABLE DATA; Schema: public; Owner: vagrant
--

COPY public."order" (id, "table", done, pay) FROM stdin;
\.


--
-- Data for Name: product; Type: TABLE DATA; Schema: public; Owner: vagrant
--

COPY public.product (name) FROM stdin;
acqua
bibite
caffe al ginseng
caffe americano
caffe d'orzo
caffe decaffeinato
caffe decaffeinato shakerato
caffe espresso
caffe freddo
caffe latte
caffe shakerato
cappuccino
cappuccino al ginseng
cappuccino d'orzo
cappuccino decaffeinato
cappuccino freddo
ciambella
cioccolata
cioccolata con panna
crema di caffe
crostata
frullati vari
insalatone
latte bianco
latte macchiato
panini assortiti
spremuta
succhi di frutta
the ed infusi
the freddo
toast
tramezzini assortiti
\.


--
-- Name: order idx_16518_primary; Type: CONSTRAINT; Schema: public; Owner: vagrant
--

ALTER TABLE ONLY public."order"
    ADD CONSTRAINT idx_16518_primary PRIMARY KEY (id);


--
-- Name: product idx_16523_primary; Type: CONSTRAINT; Schema: public; Owner: vagrant
--

ALTER TABLE ONLY public.product
    ADD CONSTRAINT idx_16523_primary PRIMARY KEY (name);


--
-- Name: idx_16515_has_products_ibfk_1; Type: INDEX; Schema: public; Owner: vagrant
--

CREATE INDEX idx_16515_has_products_ibfk_1 ON public.has_products USING btree (id);


--
-- Name: idx_16515_has_products_ibfk_2; Type: INDEX; Schema: public; Owner: vagrant
--

CREATE INDEX idx_16515_has_products_ibfk_2 ON public.has_products USING btree (name);


--
-- Name: has_products has_products_ibfk_1; Type: FK CONSTRAINT; Schema: public; Owner: vagrant
--

ALTER TABLE ONLY public.has_products
    ADD CONSTRAINT has_products_ibfk_1 FOREIGN KEY (id) REFERENCES public."order"(id) ON UPDATE CASCADE;


--
-- Name: has_products has_products_ibfk_2; Type: FK CONSTRAINT; Schema: public; Owner: vagrant
--

ALTER TABLE ONLY public.has_products
    ADD CONSTRAINT has_products_ibfk_2 FOREIGN KEY (name) REFERENCES public.product(name) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- PostgreSQL database dump complete
--

