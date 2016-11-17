**Purpose**<br>
Some scripts sending requests to outer gates, such as Amadeus,
and logging response to MongoDB.<br>
This web application analyzes this logs, calculates success responses, error responses,
persantage of error and show result as html subpage with flow charts<br>
You can set number of days for analyze, interval of averaging, start date and
end date in URL directly.<br>
Usage<br>
http(s)://_(application url)_/mongostats/galileo/days/<br>
_(number of days)_/interval/_(interval in minutes)_/
?start_date=YYYYMMDD&end_date=YYYYMMDD

