-- Prevent deletion of customers and fabrics that are referenced by order history.
-- Run after 001_modernize.sql on an existing database.

ALTER TABLE orders DROP FOREIGN KEY orders_ibfk_1;
ALTER TABLE orders
    ADD CONSTRAINT orders_ibfk_1
    FOREIGN KEY (customer_id) REFERENCES customers(customer_id)
    ON DELETE RESTRICT;

ALTER TABLE order_items DROP FOREIGN KEY order_items_ibfk_2;
ALTER TABLE order_items
    ADD CONSTRAINT order_items_ibfk_2
    FOREIGN KEY (fabric_id) REFERENCES fabrics(fabric_id)
    ON DELETE RESTRICT;
