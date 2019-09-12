SELECT
    s.id,
    s.definition,
    s.currency,
    s.piece,
    s.salary,
    s.tax,
    s.company_id,
    s.from_place,
    s.to_place,
    s.driver_id,
    s.created_date,
    c.name,
    c.reg,
    c.adresa,
    c.cif,
    c.judet,
    c.tara,
    d.name,
    d.surname,
    d.passport_no
FROM
    services as s
INNER JOIN
    companies as c ON
        s.company_id = c.id
INNER JOIN
    drivers as d ON
        s.driver_id = d.id
WHERE
    s.id = :id
    