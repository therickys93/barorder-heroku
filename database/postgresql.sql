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

--
-- Name: update_price_function(); Type: FUNCTION; Schema: public; Owner: igzedusvarvtxq
--

CREATE FUNCTION public.update_price_function() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
DECLARE
    new_price real := 0.0;
BEGIN
   new_price := (SELECT SUM(has_products.quantity * product.price) AS price FROM has_products, product WHERE has_products.name = product.name AND id = NEW.id);
   UPDATE public.order SET price = new_price WHERE id = NEW.id;
   RETURN NEW;
END;
$$;


ALTER FUNCTION public.update_price_function() OWNER TO postgres;

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
    done bigint DEFAULT 0 NOT NULL,
    pay bigint DEFAULT 0 NOT NULL,
    price real DEFAULT 0 NOT NULL
);


ALTER TABLE public."order" OWNER TO postgres;
ALTER TABLE public."order" REPLICA IDENTITY FULL;

--
-- Name: product; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.product (
    name character varying(255) NOT NULL,
    price real NOT NULL
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

COPY public.product (name, price) FROM stdin;
acqua	0.5
bibite	1
caffe al ginseng	1.5
caffe americano	1.5
caffe decaffeinato	1.20000005
caffe decaffeinato shakerato	1.29999995
caffe di orzo	1
caffe espresso	1
caffe freddo	2
caffe latte	2.5
caffe shakerato	1.5
cappuccino	2.5
cappuccino al ginseng	2.79999995
cappuccino di orzo	2.79999995
cappuccino decaffeinato	2.79999995
cappuccino freddo	2.79999995
ciambella	3
cioccolata	3.5
cioccolata con panna	4
crema di caffe	3.5
crostata	2.5
frullati vari	3
insalatone	5
latte bianco	2
latte macchiato	2.5
panini assortiti	4.5
spremuta	3.5
succhi di frutta	3
the ed infusi	2.5
the freddo	2.5
toast	5
tramezzini assortiti	4.5
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
-- Name: has_products update_price; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER update_price AFTER INSERT ON public.has_products FOR EACH ROW EXECUTE PROCEDURE public.update_price_function();


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

