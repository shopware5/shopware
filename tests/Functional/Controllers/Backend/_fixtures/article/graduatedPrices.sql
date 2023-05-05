UPDATE s_articles_prices SET `to` = '5' WHERE s_articles_prices.articledetailsID = 153;
UPDATE s_articles_prices SET `to` = '20' WHERE s_articles_prices.articledetailsID = 154;
INSERT INTO s_articles_prices (`pricegroup`, `from`, `to`, `articleID`, `articledetailsID`, `price`, `pseudoprice`, `regulation_price`, `percent`)
VALUES ('EK', 6, 'beliebig', 89, 153, 1.3361344537815, 0, 0, 20.10),
       ('EK', 21, 'beliebig', 89, 154, 1.0840336134454, 0, 0, 35.18),
       ('H', 1, '10', 89, 153, 1.99, 0, 0, 0.00),
       ('H', 11, 'beliebig', 89, 153, 0.99, 0, 0, 50.25),
       ('H', 1, '30', 89, 154, 1.99, 0, 0, 0.00),
       ('H', 31, 'beliebig', 89, 154, 0.89, 0, 0, 55.28);
