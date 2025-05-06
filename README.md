# Holdings Report (Yii2 Basic)

Welcome to **Holdings Report**, a lightweight Yii2 application that turns your Excel trade data into an interactive, three-level grouped holdings report.  You’ll be able to:

- **Import** your raw positions from an Excel file (`Client Data.xlsx`), automatically flagging any rows missing a Currency value.
- **Group** assets by **Asset Class → Asset Type → Ticker**, and see at each level the total **Entries**, **Quantity** and **Net Amount**.
- **Explore** the data instantly in the browser using **DataTables** with the **RowGroup** extension—search, sort, paginate and drill down without reloading the page.

---

## Prerequisites

Before you begin, make sure you have:

- **PHP 7.4+** with the `mbstring`, `xml` and `zip` extensions enabled
- **Composer** (https://getcomposer.org)
- **MySQL** (or MariaDB) server
- A web server (Apache/Nginx) or the built-in PHP server

---

## Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/Mohamedeid11/holdings-report-task
   cd holdings-report
   php yii client-data/import
