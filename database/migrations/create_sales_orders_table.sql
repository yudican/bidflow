-- Migration for sales_orders table
CREATE TABLE sales_orders (
    id SERIAL PRIMARY KEY,
    order_number VARCHAR(255) UNIQUE NOT NULL,
    customer_type VARCHAR(50) DEFAULT 'Accurate' NOT NULL,
    customer_no VARCHAR(255) NOT NULL,
    date_transaction DATE NOT NULL,
    term_of_payment_id INTEGER,
    reference_number VARCHAR(255),
    delivery_date DATE,
    delivery_address TEXT,
    branch_customer VARCHAR(255),
    sub_account_stock VARCHAR(50) CHECK (sub_account_stock IN ('konsi', 'non_konsi')),
    is_taxable BOOLEAN DEFAULT false,
    tax_inclusive BOOLEAN DEFAULT false,
    tax_amount DECIMAL(15,2) DEFAULT 0,
    total_tax_amount DECIMAL(15,2) DEFAULT 0,
    notes TEXT,
    subtotal DECIMAL(15,2) DEFAULT 0,
    total_discount DECIMAL(15,2) DEFAULT 0,
    grand_total DECIMAL(15,2) DEFAULT 0,
    status VARCHAR(50) DEFAULT 'draft',
    created_by VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Migration for sales_order_items table
CREATE TABLE sales_order_items (
    id SERIAL PRIMARY KEY,
    sales_order_id INTEGER NOT NULL,
    product_id INTEGER NOT NULL,
    product_code VARCHAR(255),
    product_name VARCHAR(255),
    qty INTEGER NOT NULL,
    price DECIMAL(15,2) NOT NULL,
    discount_percent DECIMAL(5,2) DEFAULT 0,
    discount_amount DECIMAL(15,2) DEFAULT 0,
    line_total DECIMAL(15,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sales_order_id) REFERENCES sales_orders(id) ON DELETE CASCADE
);

-- Create indexes for better performance
CREATE INDEX idx_sales_orders_order_number ON sales_orders(order_number);
CREATE INDEX idx_sales_orders_customer_no ON sales_orders(customer_no);
CREATE INDEX idx_sales_orders_date_transaction ON sales_orders(date_transaction);
CREATE INDEX idx_sales_orders_status ON sales_orders(status);
CREATE INDEX idx_sales_order_items_sales_order_id ON sales_order_items(sales_order_id);
CREATE INDEX idx_sales_order_items_product_id ON sales_order_items(product_id);

-- Add comments for documentation
COMMENT ON TABLE sales_orders IS 'Table for storing sales order headers';
COMMENT ON TABLE sales_order_items IS 'Table for storing sales order line items';
COMMENT ON COLUMN sales_orders.customer_type IS 'Type of customer, default is Accurate';
COMMENT ON COLUMN sales_orders.sub_account_stock IS 'Stock account type: konsi or non_konsi';
COMMENT ON COLUMN sales_orders.is_taxable IS 'Whether the order is subject to tax';
COMMENT ON COLUMN sales_orders.tax_inclusive IS 'Whether the total amount includes tax (true) or tax is added separately (false)';
COMMENT ON COLUMN sales_order_items.discount_percent IS 'Discount percentage for the item';
COMMENT ON COLUMN sales_order_items.line_total IS 'Total amount for this line item after discount';