-- Run once against an existing FabricFlow database:
-- mysql -u root fabricflowwsystem < migrations/001_modernize.sql

START TRANSACTION;

DELETE a1 FROM attendance a1
JOIN attendance a2
  ON a1.employee_id = a2.employee_id
 AND a1.date = a2.date
 AND a1.attendance_id < a2.attendance_id;

ALTER TABLE attendance
    ADD UNIQUE KEY attendance_employee_date_unique (employee_id, date);

ALTER TABLE order_items
    ADD COLUMN unit_price DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER quantity;

UPDATE order_items oi
JOIN fabrics f ON f.fabric_id = oi.fabric_id
SET oi.unit_price = f.price_per_meter
WHERE oi.unit_price = 0;

ALTER TABLE fabrics
    ADD CONSTRAINT fabrics_price_nonnegative CHECK (price_per_meter >= 0),
    ADD CONSTRAINT fabrics_stock_nonnegative CHECK (stock_quantity >= 0);

ALTER TABLE order_items
    ADD CONSTRAINT order_items_quantity_positive CHECK (quantity > 0),
    ADD CONSTRAINT order_items_price_nonnegative CHECK (unit_price >= 0);

DELETE ma1 FROM machine_assignments ma1
JOIN machine_assignments ma2
  ON ma1.machine_id = ma2.machine_id
 AND ma1.employee_id = ma2.employee_id
 AND ma1.assignment_id < ma2.assignment_id;

ALTER TABLE machine_assignments
    ADD UNIQUE KEY machine_employee_unique (machine_id, employee_id);

COMMIT;
