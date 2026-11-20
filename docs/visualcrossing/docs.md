---
title: "Timeline Weather API Documentation"
date: "2026-11-20T11:01:29+07:00"
source: "https://www.visualcrossing.com/resources/documentation/weather-api/timeline-weather-api/"
---

# Page Structure Map
```text
- Introduction
  - Free Weather API – No API Key Required
  - Ultra-Low-Latency Forecast Access
  - Key Timeline Weather API Features
  - Data Coverage
  - Output Format
- Generate Code with our AI Code Generator
- Latest features and recent changes
- Build Queries in our Interactive API Query Builder
- Creating a Timeline API request
  - Base URL
  - Endpoints
  - Main Format of the URL
- Request Parameters
  - Main Parameters (Path Parameters)
  - Additional Parameters (Query or POST Parameters)
    - Degree days parameters (Query Parameters)
- Request Examples
  - Forecast Request Example
  - Forecast Request Example using longitude and latitude
  - Date Range Request Example
  - Date Range Request Example using UNIX Time (Epoch Time)
  - Specific Time Request Example
  - Dynamic period Request Example
  - Using the options parameter to request specific data sections
  - Exact Time Intervals (Beta)
  - Using the elements parameter to modify the weather elements
  - Degree day elements list example
  - Industry elements - solar, marine and agriculture
- Response Format
  - JSON response format
  - FLAT JSON response format
- Response Elements
  - Response weather data elements
    - Location and station elements
- HTTP Response Code and Error Handling
- Using the OpenAPI Description
- Questions or need help?
```
---

### Introduction

The Visual Crossing Timeline Weather API is the primary REST endpoint for retrieving historical weather, current conditions, and forecast data through a single consistent API.

##### Free Weather API – No API Key Required

You can use the Visual Crossing Timeline Weather API for free without an API key or account registration. Access current conditions, weather forecasts, and historical weather using standard HTTP requests. Commercial use is also permitted, subject to fair-use limits managed by client IP address.

See the [Free Weather API – No Key Required guide](https://www.visualcrossing.com/resources/documentation/weather-api/free-weather-api-no-key/) for working examples, response formats, and usage guidelines.

See the [Visual Crossing Weather API](https://www.visualcrossing.com/weather-api/) page for more details of Visual Crossing’s weather API, pricing, coverage, and features.

##### Ultra-Low-Latency Forecast Access

For ultra-low-latency forecast queries, consider the [Timeline LLX endpoint](https://www.visualcrossing.com/resources/documentation/weather-api/timeline-llx-weather-api/). It provides the same core forecast data as the Timeline API, optimized for fast response times in latency-sensitive applications.

##### Key Timeline Weather API Features

-   **Simple GET Requests** – All queries use standard HTTP GET requests with clear parameters.

-   **Flexible Time Windows** – Retrieve data for any period, including historical ranges, the current date, or future forecasts.

-   **Multiple Location Options** – Query by address, latitude/longitude, postal code, or location ID.

-   **Comprehensive Weather Elements** – Access daily, hourly, and statistical data, plus alerts and astronomical details such as sunrise, sunset, and moon phase.

-   **Dynamic Date Keywords** – Use keywords like `today`, `yesterday`, or `last30days` to simplify time-based queries.

-   **Customizable Output Formats** – Choose between structured JSON for integration or CSV for direct analysis.

##### Data Coverage

The Timeline API provides global coverage with 2km accuracy in many places. The Timeline API automatically selects the best available data sources for each query to ensure accuracy and completeness. Depending on the requested time range, the response may include:

-   **Current conditions** – Real-time observations from global weather stations and models.

-   **Daily data** – Historical, forecast, and statistical weather summaries.

-   **Hourly data** – Historical observations and up to 15-day forecasts.

-   **Weather alerts** – Active warnings for the requested location and period.

-   **Astronomical data** – Sunrise, sunset, and moon phase details.

##### Output Format

All responses are returned in a consistent JSON structure, allowing applications to consume weather data without needing to manage multiple data sources or formats. For simpler integration into spreadsheets or databases, results can also be returned as CSV text.

#### Generate Code with our AI Code Generator

*Our new AI-powered code generator instantly creates working code tailored to your requirements. Try the [AI code generator tool](https://www.visualcrossing.com/support/weather-api-ai-code-generator/) today and start building faster.*

#### Latest features and recent changes

We continue to invest heavily in enhancing the Timeline Weather API and expanding its underlying data sources. Recent improvements include:

-   **Wildfire events** – include [wildfire events](https://www.visualcrossing.com/resources/documentation/weather-api/wildfire-events-in-the-timeline-weather-api/) into the events data.
-   **MCP Server (Model Context Protocol)** —[MCP server](https://www.visualcrossing.com/resources/documentation/weather-api/visual-crossing-mcp-guide/) for AI assistant integration.
-   **Multiple API Keys** – Add [multiple API keys](https://www.visualcrossing.com/resources/documentation/managing-team-users-and-api-keys/) for different environments.
-   **Historical Weather Forecast API** – the new [Historical Forecast API](https://www.visualcrossing.com/resources/documentation/weather-api/historical-forecast-api/) offers even easier access to our Historical Weather Forecast models.
-   **Weather Maps API** – see our new [Weather Maps API](https://www.visualcrossing.com/resources/documentation/weather-api/weather-maps-api/) that provides ready-to-use weather map tiles, stitched images, and matching legends suitable for web maps, dashboards, and applications.
-   **Low Latency Endpoint** – the new [timelinellx endpoint](https://www.visualcrossing.com/resources/documentation/weather-api/timeline-llx-weather-api/) provides ultra low latency responses for forecast queries. Perfect for apps, IoT and other embedding.
-   **Flat JSON output format** – the new Flat JSON output format provides for a significantly smaller data transfer size for lightning fast transmission.
-   **Improved ability to add or remove weather elements** using the add: and remove: prefixes.
-   **Radar-Based Precipitation Estimates**  
    You can now [include radar-derived precipitation data directly in your datasets](https://www.visualcrossing.com/resources/documentation/weather-data/how-to-include-weather-radar-data-in-weather-datasets/), providing improved estimates of precipitation amount and intensity.
-   **New Programmatic Dataset APIs**  
    [New APIs](https://www.visualcrossing.com/resources/documentation/weather-api/stored-dataset-api-documentation/) have been added to enable programmatic submission, modification, and download of stored datasets, streamlining automated workflows.
-   **Sub-Hourly Data Support**  
    The Timeline Weather API now supports [sub-hourly data with minute-level granularity](https://www.visualcrossing.com/resources/documentation/weather-api/sub-hourly-data-in-the-timeline-weather-api/), offering more precise short-term forecasts and observations.

For a full list of additions to the Timeline Weather API, please see the [changelog](https://www.visualcrossing.com/resources/documentation/weather-api/weather-api-changelog/).

#### Build Queries in our Interactive API Query Builder

*The [Weather Query Builder page](https://www.visualcrossing.com/weather-query-builder/) includes a full, interactive query builder so you can create queries and see the live results directly in your browser.*

### Creating a Timeline API request

#### Base URL

```
https://weather.visualcrossing.com/VisualCrossingWebServices/rest/services/timeline/
```

#### Endpoints

The possible endpoints of the Timeline Weather API requests are as follows:

-   `/timeline/[location]` – forecast queries.
-   `/timeline/[location]/[date1]/[date2]` – queries for a specific date range.

#### Main Format of the URL

All requests to the Timeline Weather API use the following the form:

```
https://weather.visualcrossing.com/VisualCrossingWebServices/rest/services/timeline/[location]/[date1]/[date2]
```

For supported free requests, no API key is necessary. Registered users can include `?key=YOUR_API_KEY` to access their account’s usage allowances and available features.

See the [Free Weather API guide](https://www.visualcrossing.com/resources/documentation/weather-api/free-weather-api-no-key/) for more information about anonymous access.

### Request Parameters

#### Main Parameters (Path Parameters)

**location (required)** – is the address, partial address or latitude,longitude location for which to retrieve weather data. You can also use US ZIP Codes. If you would like to submit multiple locations in the same request, consider our [Multiple Location Timeline Weather API](https://www.visualcrossing.com/resources/documentation/weather-api/using-the-timeline-weather-api-with-multiple-locations-in-the-same-request/).

**date1 (optional)** – is the start date for which to retrieve weather data. If a **date2** value is also given, then it represents the first date for which to retrieve weather data. If no **date2** is specified then weather data for a single day is retrieved, and that date is specified in **date1**. All dates and times are in local time of the **location** specified. Dates should be in the format yyyy-MM-dd. For example 2020-10-19 for October 19th, 2020 or 2017-02-03 for February 3rd, 2017.

Instead of an exact date, you can specify a [dynamic date period](https://www.visualcrossing.com/resources/documentation/weather-api/using-the-time-period-parameter-to-specify-dynamic-dates-for-weather-api-requests/). See below for more details. You may also supply the in “UNIX time”. In this case provide the number of seconds since 1st January 1970 UTC. For example 1612137600 for Midnight on 1st February 2021.

You can also request the information for a specific time for a single date by including time into the date1 field using the format yyyy-MM-ddTHH:mm:ss. For example 2020-10-19T13:00:00.

The results are returned in the ‘currentConditions’ field and are truncated to the hour requested (i.e. 2020-10-19T13:59:00 will return data at 2020-10-19T13:00:00).

**date2 (optional)** – is the end date for which to retrieve weather data. This value may only be used when a **date1** value is given. When both **date1** and **date2** values are given, the query is inclusive of **date2** and the weather data request period will end on midnight of the **date2** value. All dates and times are in local time of the specified location and should be in the format yyyy-MM-dd.

When no **date1** or **date2** is specified, the request will retrieve the forecast at the requested **location** for the next 15 days.

When `options=useexacttimeinterval` is specified and both date parameters include times, the end timestamp is exclusive. Date-only end values remain inclusive.

#### Additional Parameters (Query or POST Parameters)

The following are specified as HTTP query parameters (they may also be passed in a POST query).

**key (required)** – your API key. Sign up for a free account using our [Weather Data Services](https://www.visualcrossing.com/weather-query-builder/) page.

**unitGroup (optional)** – The system of units used for the output data.  
Supported values are `us`, `uk`, `metric`, and `base`. See [Unit groups and measurement units](https://www.visualcrossing.com/resources/documentation/weather-api/unit-groups-and-measurement-units/) for more information. Defaults to US system of units.

**lang (optional)** – Sets the language of the translatable parts of the output such as the conditions field. Available languages include: ar (Arabic), bg (Bulgiarian), cs (Czech), da (Danish), de (German), el (Greek Modern), en (English), es (Spanish) ), fa (Farsi), fi (Finnish), fr (French), he Hebrew), hu, (Hungarian), it (Italian), ja (Japanese), ko (Korean), nl (Dutch), pl (Polish), pt (Portuguese), ru (Russian), sk (Slovakian), sr (Serbian), sv (Swedish), tr (Turkish), uk (Ukranian), vi (Vietnamese) and zh (Chinese). In addition passing in ‘id’ will result in the raw descriptor IDs.See [How to create or modify language files](https://www.visualcrossing.com/resources/documentation/weather-api/how-to-create-or-modify-language-files/) for more information on how to help add additional languages.

**include (optional)** – Specifies the sections you would like to include in the result data. This allows you to reduce query cost and latency. Specify this as a comma separated list. For example: `&include=obs,fcst` to include the historical observations and forecast data. The options are:

-   **days** – daily data
-   **hours** – hourly data
-   **minutes** – minutely data (beta). See [Requesting sub-hourly data in the Timeline Weather API](https://www.visualcrossing.com/resources/documentation/weather-api/sub-hourly-data-in-the-timeline-weather-api/).
-   **alerts** – weather alerts
-   **current** – current conditions or conditions at requested time.
-   **events** – [historical events such as a hail, tornadoes, wind damage and earthquakes](https://www.visualcrossing.com/resources/documentation/weather-101/how-to-retrieve-hail-tornado-wind-damage-and-earthquakes-events-in-the-weather-api/) (not enabled by default)
-   **obs** – historical observations from weather stations
-   **remote** – historical observations from remote source such as satellite or radar
-   **fcst** – forecast based on 16 day models.
-   **normals** – include statistical normals for each day of the request. The normals include the minimum, mean and maximum value.
-   **stats** – historical statistical normals and daily statistical forecast
-   **statsfcst** – use the full statistical forecast information for dates in the future beyond the current model forecast. Permits hourly statistical forecast.

**elements (optional)** – Specifies the specific weather elements you would like to include in the response as a comma separated list. For example, `&elements=tempmax,tempmin,temp` will request the only the tempmax, tempmin and temp response elements. For the full list of available elements, see the response below.

**options (optional)** – Specifies additional options on the requests to either indicate the type of data or format of the output. Supported values include:

-   **nonulls** – remove all null values from the JSON response
-   **noheaders** – removes the header row from the CSV response
-   **useexacttimeinterval** – Restricts hourly and minute data to a requested time interval. See Exact Time Intervals (Beta).

**contentType (optional)** – indicates the output format for the API. By default the output is formated in JSON. You can also set `contentType=csv` to retrieve CSV formatted data.Note that CSV format does not support the full JSON output due to the format limitations. You must use the **include** parameter to indicate which section you would like to retrieve. CSV include parameters support are days, hours, alerts, events and current.

**iconSet (optional)** – used to choose which icons IDs are populated. For more information see [Defining the icon set parameter in the Weather API](https://www.visualcrossing.com/resources/documentation/weather-api/defining-icon-set-in-the-weather-api/).

**timezone** **(optional)** – specifies the timezone of the input and result dates and times. When not specified, all date times are considered local times. If you would like to specify that all dates are entered as UTC dates and times, use `timezone=Z` parameter.

**maxDistance (optional)** The maximum distance in meters used to search for local weather stations ( By default, 50 miles or approximately 80km (80,467m). This setting is combined with the `maxStations` parameter to find local weather stations.

**maxStations (optional)** The maximum number of weather stations used to calculate a weather record (default 3). Closer weather stations are weighted significantly more heavily than farther stations.

**elevationDifference (optional)** The maximum elevation difference in meters between the requested location and weather stations. Any weather station that is either lower or higher than more than this setting will be excluded. Default is turned off.

**locationNames (optional)** provides alternative name for the location requested. This is typically used by users who need to be able to join the API results back to a dataset. For example, you may pass a store database ID in this parameter so that you can populate the weather forecast for that store.

**forecastBasisDate (optional, requires historical forecast license)** – specifies the date when the weather forecast model was run. For example, if you specify `2021-06-01`, then the data from 2021-06-01 to 2021-06-15 will use the weather forecast produced on June 1st, 2021. For more information see [How to query weather forecasts from the past](https://www.visualcrossing.com/resources/documentation/weather-data/how-to-query-weather-forecasts-from-the-past-historical-forecasts/).

**forecastBasisDay (optional, requires historical forecast license)** – specifies the forecast day for the date1 parameter above. For example, if you specify the start date as 2021-06-05 and a forecastBasisDay value of `5`, then the value for 2021-06-06 will be the 5th day forecast (i.e. it will be based on the weather forecast produced five days earlier (2021-06-01). You can use this parameter to quickly find the forecast that was predicted for a particular day on multiple days (for example the 5, 3 and 1 day forecast). For more information see [How to query weather forecasts from the past](https://www.visualcrossing.com/resources/documentation/weather-data/how-to-query-weather-forecasts-from-the-past-historical-forecasts/).

**forecastDataset** (optional, requires forecast data access) – overrides the automatic data-source selection and returns the forecast from one or more specific underlying weather models instead of the default Visual Crossing blended forecast.

By default, the Timeline API automatically combines the best available data sources for each location and time (observations, a multi-model blended forecast, and statistical forecast) into a single, seamless result. The forecastDataset parameter lets you bypass this blend and request the raw output of an individual model. This is useful for model comparison, verification, and applications that require a single, consistent forecast source.

Specify one model ID, or a comma-separated list of model IDs to retrieve several models in the same request. For example:

`&forecastDataset=gfs`

`&forecastDataset=gfs,ecmwf,hrrr`

Commonly available model IDs include:

-   `gfs` – NOAA Global Forecast System (global)
-   `gfes` – NOAA GFS Ensemble (global)
-   `nam` – NOAA North American Mesoscale model
-   `hrrr` – NOAA High-Resolution Rapid Refresh (short-range, CONUS)
-   ndfd – US National Digital Forecast Database
-   `ecmwf` – ECMWF forecast (global)
-   `ecmwfaifs` – ECMWF AI/ML forecast (AIFS)
-   `iconeu` – DWD ICON-EU (Europe)
-   `iconglobal` – DWD ICON (global)
-   `icond2` – DWD ICON-D2 (high-resolution, Central Europe)
-   `ukmo_global` – UK Met Office global model
-   `gdps` – Environment Canada Global Deterministic Prediction System
-   `usblended` / `vcusblended` – US regional blended forecasts
-   `era5core` – ECMWF ERA5 reanalysis (historical dates only)

When forecastDataset is omitted, the standard blended forecast is returned. When it is supplied, only the requested model(s) are returned.

**Important limitations**

Because you are requesting a specific model rather than the automatically blended result, the following limitations apply:

1.  **Coverage is not global for every model.** Many models are regional (for example, `hrrr`, `nam`, ndfd, `usblended`, and `vcusblended` cover the United States, while `iconeu` and `icond2` cover Europe). Requesting a model outside its geographic domain may return empty or partial data.
2.  **Time ranges vary by model.** Each model has its own forecast horizon and, for some, a limited historical window. Short-range models (such as `hrrr`) cover only the near term, while reanalysis datasets (such as `era5core`) are available only for historical dates and not for the future. Portions of your requested date range that fall outside a model’s available period may be empty.
3.  **Models may be temporarily unavailable for operational reasons.** Upstream data feeds can be delayed, incomplete, or offline, and models are periodically added, retired, or changed. A model that returns data today may return partial or no data during such periods. Applications should handle empty results gracefully and should not assume any single model is available at all times.

**Example**

Retrieve the hourly GFS forecast for London so they can be compared side by side:

`https://weather.visualcrossing.com/VisualCrossingWebServices/rest/services/timeline/London,UK?key=YOUR_API_KEY&include=hours&forecastDataset=gfs`

##### Degree days parameters (Query Parameters)

**degreeDayTempFix** – The temperature at which to start the degree day season if no season start and end dates are specified. For example, if the fix temperature is 32F (OC), then the cumulative degree days will reset to zero on the last 32F temperature of the winter. This defines the growing season for growing degree days.

**degreeDayStartDate** – Fixes the start of the degree day season based on fixed dates of the format yyyy-M-d (eg 1990-3-1 for 1st March). Default=not specified.

**degreeDayTempMaxThreshold** – Defines the maximum temperature that is considered for the calculation. Any temperature above this temperature will be set to the maximum threshold temperature. Default value=not set.

**degreeDayTempBase** – The degree day base temperature. Default value is set to 10C/50F

**degreeDayInverse** – Calculate the inverse degree days so colder temperatures contribute more degree days. Used for heating degree days. Default=false.

**degreeDayMethod** – The method used for calculating the degree days. Values include average, sine, doublesine, triangle and doubletriangle. Default value is set to average.

### Request Examples

#### Forecast Request Example

The following will retrieve the weather forecast for London, United Kingdom for the next 15 days, starting at midnight at the start of the current day (local time of the requested location).

```
https://weather.visualcrossing.com/VisualCrossingWebServices/rest/services/timeline/London,UK?key=YOUR_API_KEY
```

#### Forecast Request Example using longitude and latitude

#### Date Range Request Example

The following will retrieve the weather data for London, UK from October 1st, 2020 to December 31st, 2020 (inclusive).

Assuming the current date is November 1st, 2020, the result will include historical observations from October 1st to 31st, then 15 days of weather forecast and finally the remaining days will include the statistical forecast based on processing years of historical observations.

#### Date Range Request Example using UNIX Time (Epoch Time)

The following shows the same query using UNIX format of seconds since the 1970 UNIX time epoch. Note that these times are seconds in the UTC (GMT/Z) time zone.

#### Specific Time Request Example

The following will retrieve the weather data for London, UK for the 15th December 2020 and will request the current conditions property be populated using the conditions at 13:00 local time (1pm local time).

The above example will include the daily and hourly detail for the day of the current conditions (2020-12-15 in this case). This query cost will therefore be 24. If you only need the specific time data, and don’t need the hourly detail, you can reduce the query costing using the include parameter. In this case the query cost will be one.

#### Dynamic period Request Example

Rather the specify and date range, you can also specify and [dynamic period](https://www.visualcrossing.com/resources/documentation/weather-api/using-the-time-period-parameter-to-specify-dynamic-dates-for-weather-api-requests/). A request based on a dynamic period will automatically adjust based on the period. In this example, we will use the dynamic period value “last30days” to retrieve data for the most recent 30 days.

```
https://weather.visualcrossing.com/VisualCrossingWebServices/rest/services/timeline/London,UK/last30days?key=YOUR_API_KEY
```

Other dynamic period values include “today”, “yesterday”, and “lastyear”. For a complete list of options and additional example, please see the [dynamic period](https://www.visualcrossing.com/resources/documentation/weather-api/using-the-time-period-parameter-to-specify-dynamic-dates-for-weather-api-requests/) article.

#### Using the options parameter to request specific data sections

By default both daily and hourly data is included in the response along with all the response weather data elements (see below). To reduce the size of the result JSON for lower query cost, reduced network transfer and faster client processing, the `options` and `elements` parameters may be used.

This example requests only the daily data:

```
https://weather.visualcrossing.com/VisualCrossingWebServices/rest/services/timeline/London,UK/last30days?key=YOUR_API_KEY&include=days
```

This example requests only the current conditions data:

```
https://weather.visualcrossing.com/VisualCrossingWebServices/rest/services/timeline/London,UK/last30days?key=YOUR_API_KEY&include=current
```

#### Exact Time Intervals (Beta)

By default, Timeline Weather API requests return weather data for complete days, even when the requested start and end dates include specific times.

The `useexacttimeinterval` option allows you to request hourly and minute-level weather data for a specific time window, rather than returning records for entire days. This is useful for applications that retrieve short periods of historical weather data, poll for recent observations, or need to minimize response sizes and query costs.

To enable this feature, include the following option in your API request:

`options=useexacttimeinterval`

The option can also be combined with other options, such as `minuteinterval_5`.

**How time intervals work**

-   The start timestamp is inclusive and the end timestamp is exclusive.

-   Minute records follow their existing time alignment and are included when their timestamps fall within the requested interval.

-   Hourly records are included whenever any portion of the hour overlaps the requested interval.

-   Daily summaries continue to represent complete days and are not recalculated for the selected interval.

This feature is available in both the Timeline Weather API and Timeline LLX API.

**Backward compatibility:** This option is disabled by default. Existing requests continue to behave as before. Use the option name alone.

#### Using the elements parameter to modify the weather elements

This example requests the daily data with only temperature elements:

```
https://weather.visualcrossing.com/VisualCrossingWebServices/rest/services/timeline/London,UK/last30days?key=YOUR_API_KEY&include=days&elements=tempmax,tempmin,temp
```

You may also add or remove elements from the default list of elements. The helps avoid listing a long list of elements when you only require to add or remove a small number.

```
https://weather.visualcrossing.com/VisualCrossingWebServices/rest/services/timeline/London,UK/last30days?key=YOUR_API_KEY&include=days&elements=add:aqius,remove:windgust
```

#### Degree day elements list example

The following includes the daily ‘degree days’ information for the requested location and date range (in this case the last 30 days).

```
https://weather.visualcrossing.com/VisualCrossingWebServices/rest/services/timeline/London,UK/last30days?
unitGroup=us&key=YOUR_API_KEY&include=days&elements=datetime,tempmax,tempmin,degreedays,accdegreedays&degreeDayTempMaxThreshold=86&degreeDayTempBase=50
```

For more information on our growing, cooling and heating degree day options, please see our [degree day page](https://www.visualcrossing.com/resources/documentation/weather-api/degree-day-weather-api/).

#### Industry elements - solar, marine and agriculture

The Timeline API can be extended to include advanced agriculture, horticulture and energy elements. These include evapotranspiration, soil temperature & moisture, advanced solar radiation and higher altitude wind speeds and directions.

To include these advanced industry elements, please see our [Agriculture and Horticulture](https://www.visualcrossing.com/resources/documentation/weather-api/agriculture-elements-in-the-timeline-weather-api/) and [Wind and Solar Energy](https://www.visualcrossing.com/resources/documentation/weather-api/energy-elements-in-the-timeline-weather-api/) pages.

### Response Format

The Timeline Weather API offers the following output formats. These are specified via the ‘contentType’ parameter (for example &contentType=json)

-   json – an object-based JSON format suitable for easily parsing into a object structure.
-   flatjson – an alternative JSON format that is designed to be be as light-weight as possible.
-   csv – comma separated values where each column is a weather element.

Both JSON and FLATJSON formats support including multiple sections of data such as days, hours, minutes etc. into a single request. The CSV format requires the include parameter to indicate which section should be included (for example ‘include=days’).

#### JSON response format

```
{
  "queryCost": 168,
  "latitude": 38.9598,
  "longitude": -77.3545,
  "resolvedAddress": "Reston, VA, United States",
  "address": "Reston,VA",
  "timezone": "America/New_York",
  "tzoffset": -4.0,

  "days": [
    {
      "datetime": "2025-06-01",
      "tempmax": 68.0,
      "tempmin": 44.9,
      "temp": 58.6,
      "humidity": 56.4,
      "precip": 0.0,
      "windspeed": 14.4,
      "pressure": 1009.5,
      "cloudcover": 61.6,
      "sunrise": "05:45:46",
      "sunset": "20:29:24",
      "conditions": "Partially cloudy",
      "icon": "partly-cloudy-day",
      "stations": ["KIAD", "KJYO"],
      "hours": [
        {
          "datetime": "00:00:00",
          "temp": 52.9,
          "humidity": 59.3,
          "dew": 39.1,
          "windspeed": 5.3,
          "pressure": 1006.3,
          "cloudcover": 0.0,
          "conditions": "Clear",
          "icon": "clear-night"
        },
        {
          "datetime": "12:00:00",
          "temp": 65.8,
          "humidity": 43.1,
          "windspeed": 13.0,
          "pressure": 1010.5,
          "conditions": "Partially cloudy",
          "icon": "partly-cloudy-day"
        }
      ]
    },
    {
      "datetime": "2025-06-02",
      "tempmax": 74.0,
      "tempmin": 47.0,
      "temp": 61.7,
      "humidity": 53.5,
      "precip": 0.0,
      "windspeed": 10.0,
      "pressure": 1016.7,
      "cloudcover": 32.1,
      "sunrise": "05:45:24",
      "sunset": "20:30:04",
      "conditions": "Partially cloudy",
      "icon": "partly-cloudy-day"
    }
  ],

  "stations": {
    "KIAD": {
      "name": "Washington Dulles Intl AP",
      "latitude": 38.95,
      "longitude": -77.45,
      "distance": 8339.0
    },
    "KJYO": {
      "name": "Leesburg Executive Airport",
      "latitude": 39.08,
      "longitude": -77.56,
      "distance": 22247.0
    }
  }
}
```

The response begins with a set of properties that describe the location and request details. These include the requested location, the resolved address, the latitude and longitude, the time zone name, and the time zone offset in hours. Note that the time zone offset may vary within a dataset due to daylight saving time changes. When this occurs, a `tzoffset` property is included within the relevant `day`, `hour`, or `currentConditions` weather data object.

The JSON format represents weather data as a hierarchical structure of objects. It begins with an array of `days`, and each day may contain arrays of `hours` and `events` if those data sections are requested. When minute-level data is included, it appears within each `hour` object. For each time level (day, hour, minute, etc.), weather element values are provided as individual properties.

If the request includes the current date, the response will also contain the `currentConditions` object and any active weather alerts for the specified location.

#### FLAT JSON response format

```
{
  "queryCost": 168,
  "latitude": 38.9598,
  "longitude": -77.3545,
  "resolvedAddress": "Reston, VA, United States",
  "address": "Reston,VA",
  "timezone": "America/New_York",
  "tzoffset": -4.0,
  "description": null,

  "days": {
    "datetime": ["2025-06-01", "2025-06-02"],
    "datetimeEpoch": [1748750400, 1748836800],
    "tempmax": [68.0, 74.0],
    "tempmin": [44.9, 47.0],
    "temp":    [58.6, 61.7],
    "humidity": [56, 54],
    "precip":   [0.0, 0.0],
    "precipprob": [0, 0],
    "windgust": [26.0, 19.7],
    "windspeed": [14.4, 10.0],
    "winddir": [258, 299],
    "pressure": [1010, 1017],
    "cloudcover": [62, 32],
    "visibility": [9.9, 9.9],
    "sunrise": ["05:45:46", "05:45:24"],
    "sunriseEpoch": [1748771146, 1748857524],
    "sunset": ["20:29:24", "20:30:04"],
    "sunsetEpoch": [1748824164, 1748910604],
    "conditions": [0, 0],
    "description": [0, 1],
    "icon": [0, 0],
    "preciptype": [null, null],
    "stations": [
      ["72405503714", "KIAD", "USW00093738", "C3816", "72403093738", "KJYO"],
      ["72405503714", "KIAD", "USW00093738", "C3816", "72403093738", "KJYO", "72033493764"]
    ]
  },

  "hours": {
    "datetime": ["2025-06-01T00", "2025-06-01T01"],
    "datetimeEpoch": [1748750400, 1748754000],
    "temp": [52.9, 50.0],
    "feelslike": [52.9, 50.0],
    "humidity": [59, 66],
    "dew": [39.1, 39.1],
    "precip": [0.0, 0.0],
    "precipprob": [0, 0],
    "windgust": [21.7, 19.7],
    "windspeed": [5.3, 0.9],
    "winddir": [331, 265],
    "pressure": [1006, 1006],
    "visibility": [9.9, 9.9],
    "cloudcover": [0, 0],
    "solarradiation": [0, 0],
    "solarenergy": [0.0, 0.0],
    "uvindex": [0, 0],
    "preciptype": [null, null],
    "conditions": [2, 2],
    "icon": [3, 3],
    "stations": [
      ["72405503714", "KIAD", "USW00093738", "C3816", "72403093738", "KJYO"],
      ["72405503714", "KIAD", "USW00093738", "C3816", "72403093738", "KJYO"]
    ]
  },

  "dictionaries": {
    "conditions": ["Partially cloudy", "Rain, Partially cloudy", "Clear", "Overcast", "Rain, Overcast"],
    "description": ["Partly cloudy throughout the day.", "Becoming cloudy in the afternoon.", "Partly cloudy with morning rain.", "Clear conditions throughout the day.", "Partly cloudy with rain."],
    "icon": ["partly-cloudy-day", "rain", "clear-day", "clear-night", "partly-cloudy-night", "cloudy"]
  }
}
```

Like JSON format, the response begins with top-level request and location metadata:

-   address (requested location text)
-   resolvedAddress (resolved location label)
-   latitude and longitude
-   timezone (IANA time zone)
-   tzoffset (UTC offset in hours)

**Time zone behavior**  
The top-level tzoffset is the default offset for the response period. If daylight saving time or another offset transition occurs within the returned data, a tzoffset value is included on the affected day, hour, minute, or currentConditions record.  
Client rule: use the record-level tzoffset when present; otherwise use the top-level tzoffset.

**Columnar structure**  
FlatJSON is a columnar format, not an array of day or hour objects.

-   days, hours, and minutes are objects
-   each weather element is an array within that object
-   arrays are parallel by index
-   each index represents one time step

**Example**  
days.datetime index i, days.tempmax index i, and days.humidity index i all describe the same day.

**Aggressive null pruning**  
To keep payloads as small as possible, FlatJSON removes fields that contain only null values in a section.

-   If every value for a field is null, that field may be omitted entirely
-   Omitted fields should be interpreted as no available non-null values, not as zero or false
-   Clients must check field existence before reading array values

**Example**  
If no returned day has a preciptype value, the days preciptype field may be absent.

**Dictionary-coded string fields**  
To reduce repeated string values, selected fields use integer codes plus lookup tables in dictionaries.  
Common dictionary-backed fields include:

-   conditions
-   description
-   icon

**Decoding rule**  
If a field value is numeric (for example, days conditions index i equals 2), resolve it using the matching dictionary array index (dictionaries conditions index 2).

**Client parsing checklist**

1.  Treat section fields as optional and sparse.
2.  Validate field existence before indexing.
3.  Align values by shared index only within the same section.
4.  Decode coded fields using dictionaries when present.
5.  Prefer per-record tzoffset when available.

### Response Elements

#### Response weather data elements

**cloudcover** – how much of the sky is covered in cloud ranging from 0-100%

**conditions** – textual representation of the weather conditions. See [Weather Data Conditions](https://www.visualcrossing.com/resources/documentation/weather-api/weather-condition-fields/).

**description** – longer text descriptions suitable for displaying in weather displays. The descriptions combine the main features of the weather for the day such as precipitation or amount of cloud cover. Daily descriptions are provided for historical and forecast days. When the timeline request includes the model forecast period, a seven day outlook description is provided at the root response level.

**datetime** – ISO 8601 formatted date, time or datetime value indicating the date and time of the weather data in the local time zone of the requested location. See [Dates and Times in the Weather API](https://www.visualcrossing.com/resources/documentation/weather-api/date-and-times-in-the-weather-api/) for more information.

**datetimeEpoch** – number of seconds since 1st January 1970 in UTC time

**tzoffset** – the time zone offset in hours. This will only occur in the data object if it is different from the global time zone offset.

**dew** – dew point temperature

**feelslike** – what the temperature feels like accounting for heat index or wind chill. Daily values are average values (mean) for the day.

**feelslikemax** (day only) – maximum feels like temperature at the location.

**feelslikemin** (day only) – minimum feels like temperature at the location.

**hours** – array of hourly weather data objects. This is a child of each of the daily weather object when hours are selected.

**humidity** – relative humidity in %

**icon** – a fixed, machine readable summary that can be used to display an icon

**moonphase** – represents the fractional portion through the current moon lunation cycle ranging from 0 (the new moon) to 0.5 (the full moon) and back to 1 (the next new moon). See [How to include sunrise, sunset, moon phase, moonrise and moonset data into your API requests](https://www.visualcrossing.com/resources/documentation/weather-api/how-to-include-sunrise-sunset-and-moon-phase-data-into-your-api-requests/)

**normal** – array of normal weather data values – Each weather data normal is an array of three values representing, in order, the minimum value over the statistical period, the mean value, and the maximum value over the statistical period.

**offsetseconds** (hourly only) – time zone offset for this weather data object in seconds – This value may change for a location based on daylight saving time observation.

**precip** – the amount of liquid precipitation that fell or is predicted to fall in the period. This includes the liquid-equivalent amount of any frozen precipitation such as snow or ice.

**precipremote** – radar estimated precipitation amount. See [How to Include Weather Radar Data in Weather Datasets](https://www.visualcrossing.com/resources/documentation/weather-data/how-to-include-weather-radar-data-in-weather-datasets/) for more information.

**precipcover** (days only) – the proportion of hours where there was non-zero precipitation

**precipprob** (forecast only) – the likelihood of measurable precipitation ranging from 0% to 100%

**preciptype** – an array indicating the type(s) of precipitation expected or that occurred. Possible values include rain, snow, freezingrain and ice.

**reflectivity** – (minutes only) estimates of the radar-based reflectivity values indicating precipitation intensity. See [How to Include Weather Radar Data in Weather Datasets](https://www.visualcrossing.com/resources/documentation/weather-data/how-to-include-weather-radar-data-in-weather-datasets/) for more information.

**pressure** – the sea level atmospheric or barometric pressure in millibars (or hectopascals)

**snow** – the amount of snow that fell or is predicted to fall

**snowdepth** – the depth of snow on the ground

**source** – the type of weather data used for this weather object. – Values include historical observation (“obs”), forecast (“fcst”), historical forecast (“histfcst”) or statistical forecast (“stats”). If multiple types are used in the same day, “comb” is used. Today a combination of historical observations and forecast data.

**stations** (historical only) – the weather stations used when collecting an historical observation record

**sunrise** (day only) – The formatted time of the sunrise (For example “2022-05-23T05:50:40”). See [How to include sunrise, sunset, moon phase, moonrise and moonset data into your API requests](https://www.visualcrossing.com/resources/documentation/weather-api/how-to-include-sunrise-sunset-and-moon-phase-data-into-your-api-requests/)

**sunriseEpoch** – sunrise time specified as number of seconds since 1st January 1970 in UTC time

**sunset** – The formatted time of the sunset (For example “2022-05-23T20:22:29”). See [How to include sunrise, sunset, moon phase, moonrise and moonset data into your API requests](https://www.visualcrossing.com/resources/documentation/weather-api/how-to-include-sunrise-sunset-and-moon-phase-data-into-your-api-requests/)

**sunsetEpoch** – sunset time specified as number of seconds since 1st January 1970 in UTC time

**moonrise** (day only, optional) – The formatted time of the moonrise (For example “2022-05-23T02:38:10”). See [How to include sunrise, sunset, moon phase, moonrise and moonset data into your API requests](https://www.visualcrossing.com/resources/documentation/weather-api/how-to-include-sunrise-sunset-and-moon-phase-data-into-your-api-requests/)

**moonriseEpoch** (day only, optional) – moonrise time specified as number of seconds since 1st January 1970 in UTC time

**moonset** (day only, optional) – The formatted time of the moonset (For example “2022-05-23T13:40:07”)

**moonsetEpoch** (day only, optional) – moonset time specified as number of seconds since 1st January 1970 in UTC time

**civil, nautical, and astronomical twilight (day only, optional)** – The formatted local times for the start of morning twilight and end of evening twilight. Available elements include `civildawn`, `civildusk`, `nauticaldawn`, `nauticaldusk`, `astronomicdawn`, and `astronomicdusk`. See [Daylight, Civil, Nautical, and Astronomical Twilight](https://www.visualcrossing.com/resources/documentation/weather-data/daylight-civil-nautical-astronomical-twilight/).

**twilight epoch values (day only, optional)** – Twilight times specified as the number of seconds since January 1, 1970 in UTC. Available elements include `civildawnEpoch`, `civilduskEpoch`, `nauticaldawnEpoch`, `nauticalduskEpoch`, `astronomicdawnEpoch`, and `astronomicduskEpoch`. See [Daylight, Civil, Nautical, and Astronomical Twilight](https://www.visualcrossing.com/resources/documentation/weather-data/daylight-civil-nautical-astronomical-twilight/).

**temp** – temperature at the location. Daily values are average values (mean) for the day.

**tempmax** (day only) – maximum temperature at the location.

**tempmin** (day only) – minimum temperature at the location.

**uvindex** – a value between 0 and 10 indicating the level of ultra violet (UV) exposure for that hour or day. 10 represents high level of exposure, and 0 represents no exposure. The UV index is calculated based on amount of short wave solar radiation which in turn is a level the cloudiness, type of cloud, time of day, time of year and location altitude. Daily values represent the maximum value of the hourly values.

**uvindex2** (optional, 5 day forecast only) – an alternative UV index element that uses the algorithms and models used by the [US National Weather Service](https://www.cpc.ncep.noaa.gov/products/stratosphere/uv_index/uv_global.shtml). In order to maintain backwards compatibility, this UV index element is deployed as a new, optional element ‘uvindex2’ and may be requested using the elements parameter.

**visibility** – distance at which distant objects are visible

**winddir** – direction from which the wind is blowing

**windgust** – instantaneous wind speed at a location – May be empty if it is not significantly higher than the wind speed. Daily values are the maximum hourly value for the day.

**windspeed** – the sustained wind speed measured as the average windspeed that occurs during the preceding one to two minutes. Daily values are the maximum hourly value for the day.

**windspeedmax** (day only, optional)– maximum wind speed over the day.

**windspeedmean** (day only , optional ) – average (mean) wind speed over the day.

**windspeedmin** (day only , optional ) – minimum wind speed over the day.

**solarradiation** – (W/m²) the average solar radiation power over the hour ending at the indicated time (preceding-hour average) for the observation or forecast. See the [full solar radiation data documentation](https://www.visualcrossing.com/resources/documentation/weather-data/how-to-obtain-solar-radiation-data/) and [Wind and Solar Energy](https://www.visualcrossing.com/resources/documentation/weather-api/energy-elements-in-the-timeline-weather-api/) pages .

**solarenergy** – (MJ /m2 ) indicates the total energy from the sun that builds up over an hour or day. See the [full solar radiation data documentation](https://www.visualcrossing.com/resources/documentation/weather-data/how-to-obtain-solar-radiation-data/) and [Wind and Solar Energy](https://www.visualcrossing.com/resources/documentation/weather-api/energy-elements-in-the-timeline-weather-api/) pages .

**severerisk (forecast only)** – a value between 0 and 100 representing the risk of convective storms (e.g. thunderstorms, hail and tornadoes). Severe risk is a scaled measure that combines a variety of other fields such as the convective available potential energy (CAPE) and convective inhibition (CIN), predicted rain and wind. Typically, a severe risk value less than 30 indicates a low risk, between 30 and 70 a moderate risk and above 70 a high risk.

**cape (forecast only)** – convective available potential energy. This is a numbering indicating amount of energy available to produce thunderstorms. A higher values indicates a more unstable atmosphere capable of creating stronger storms. Values lower than 1000 J/kg indicate generally low instability, between 1000-2500 J/kg medium instability and 2500-4000 J/kg high instability. Values greater than 4000 J/kg indicating an extremely unstable atmosphere.

**cin (forecast only)** – convective inhibition. A number representing the level of atmospheric tendency to prevent instability and therefore prevent thunderstorms.

**degreedays** (day only) – optional elements indicating the number of degree days for this date. See the [degree days API](https://www.visualcrossing.com/resources/documentation/weather-api/degree-day-weather-api/) for more information on degree days. To turn degree days and degree day accumulation on, use the elements parameter. For example, elements=datetime,tempmax,tempmin,degreedays,accdegreedays.

To convert existing Dark Sky API parameters to the Timeline Weather API, see *[How to replace the Dark Sky API with the Timeline Weather API](https://www.visualcrossing.com/resources/blog/how-to-replace-the-dark-sky-api-using-the-visual-crossing-timeline-weather-api/)*.

##### Location and station elements

The following are available in the JSON response type to provide information about the requested location:

**queryCost** – The record cost of this query. See [what is a record](https://www.visualcrossing.com/resources/documentation/weather-data/what-exactly-is-a-weather-record/) for more information.

**latitude,longitude** – the latitude and longitude of the requested location. This is provided in decimal degrees.

**resolvedAddress** – if the requested location was made by address, the address that is found using our internal geocoding engine.

**address** – the requested location text from the request.

**timezone** – the timezone of the location. See [Date and Times in the Weather API](https://www.visualcrossing.com/resources/documentation/weather-api/date-and-times-in-the-weather-api/) for more information.

**tzoffset** – the timezone offset from UTC time for the first record of the data. This may change throughout the dataset if there is a daylight savings change during the requested date range.

**elevation (optional)** – the elevation of the requested location. This is an optional element requested by including the elements parameter: (see [Adding and Removing Elements](https://www.visualcrossing.com/resources/documentation/weather-data/adding-and-removing-elements-from-your-weather-query/)).

### HTTP Response Code and Error Handling

The API communicates error codes through the HTTP response code. In addition, the body the response will normally include additional error information indicating the cause of the error. The possible HTTP response status codes include:

200 OK – a successfully processed request

400 BAD\_REQUEST – The format of the API is incorrect or an invalid parameter or combination of parameters was supplied

401 UNAUTHORIZED – There is a problem with the API key, account or subscription. May also be returned if a feature is requested for which the account does not have access to.

404 NOT\_FOUND – The request cannot be matched to any valid API request endpoint structure.

429 TOO\_MANY\_REQUESTS – The account has exceeded their assigned limits. See [What is the cause of “Maximum concurrent jobs has been exceeded”, HTTP response 429](https://www.visualcrossing.com/resources/documentation/weather-api/what-is-the-cause-of-maximum-concurrent-jobs-has-been-exceeded-http-response-429/)

500 INTERNAL\_SERVER\_ERROR – A general error has occurred processing the request.

For general information on debugging API queries, please see: [How to debug problems when running weather API queries in code](https://www.visualcrossing.com/resources/documentation/weather-api/how-to-debug-problems-when-running-weather-api-queries-in-code/)

### Using the OpenAPI Description

### Questions or need help?

Our [AI Code generato](https://www.visualcrossing.com/support/weather-api-ai-code-generator/)r provides real-time, 24×7 access to code generation and other questions related to the Weather API usage.

If you have a additional questions, please post on our [actively monitored forum](https://support.visualcrossing.com/hc/en-us/community/topics/360001293151-Weather) for the fastest replies. You can also contact us via our [support site](https://www.visualcrossing.com/support/)

---
title: "Weather Maps API – Tiles, Images, and Legends"
date: "2026-11-20T11:02:51+07:00"
source: "https://www.visualcrossing.com/resources/documentation/weather-api/weather-maps-api/"
---

# Page Structure Map
```text
- Overview and key features
  - Live interactive Weather Maps using the Weather Map API
  - Free Weather Maps API – No API Key Required
- Weather Map API Integration Samples
- Base URL
- Endpoint summary
- Authentication
- Supported elements and sources
- Tile endpoint – Slippy-map tiles
  - URL format
  - Query parameters
  - Example – latest precipitation tile
- Image endpoint – stitched static maps
  - URL format
  - Query parameters
  - Example – global precipitation image
  - Example – high-zoom image for a city
  - URL format
  - Query parameters
  - Typical legend response structure
- Using the Weather Maps API from a map library
- Notes and best practices
- Access, pricing, and radar data sources
  - Is the Weather Maps API included with my Weather API account?
  - How are Weather Maps API requests billed?
  - How are stitched static image requests billed?
- What sources of data are used for the weather maps?
  - The precipitation composite layer
  - Radar and remote-sensing observations
  - Forecast models
  - Weather alerts
  - How the source is chosen
- Questions or need help?
```
---

The Weather Maps API is a new set of endpoints that provide ready-to-use weather map tiles, stitched images, and matching legends suitable for web maps, dashboards, and applications. Instead of downloading raw gridded data and rendering it yourself, you can request pre-rendered map imagery for supported weather elements such as precipitation composites or surface temperature.

This article describes the v1 map endpoints:

-   Slippy-map tiles (`tile`) for interactive web maps
-   Static stitched images (`image`) for dashboards and reports
-   Legends (`legend`) that describe the color scales for each map layer

The Weather Maps API is built on the same weather data engine as our other services and uses the same API key and account limits.

## Overview and key features

The Weather Maps API is designed for:

-   Interactive web maps using XYZ/Slippy-map tiles
-   Server-generated static images for dashboards, emails, and reports
-   Consistent, machine-readable legends for custom map renderers

Key features:

-   Simple HTTP GET endpoints using standard query parameters
-   Support for multiple models and sources via a logical `source` parameter
-   High-quality image output in WebP, PNG, or JPEG
-   Consistent color bins and labels returned by the legend endpoint
-   Built-in HTTP caching using `ETag` and `Last-Modified` headers for time-fixed requests

### Live interactive Weather Maps using the Weather Map API

To view the weather maps in action, we have a full developer [weather map example](https://www.visualcrossing.com/weather-map/).

### Free Weather Maps API – No API Key Required

The Visual Crossing Weather Maps API supports free access without an API key or account registration. You can retrieve weather map tiles, static images, and legends for use in websites, dashboards, and commercial applications.

Anonymous requests use fair-use limits managed by client IP address. For examples and details on getting started, see the [Free Weather API – No Key Required guide](https://www.visualcrossing.com/resources/documentation/weather-api/free-weather-api-no-key/).

## Weather Map API Integration Samples

Our [Weather Map API Integration Samples](https://www.visualcrossing.com/resources/documentation/weather-api/weather-map-api-integration-samples/) article includes a set of basic map integration examples to get you started quickly.

## Base URL

All Weather Maps API endpoints share the following base URL:

```
https://maps.visualcrossing.com/VisualCrossingWebServices/rest/api/v1/map/
```

## Endpoint summary

Weather Maps API v1 exposes three main endpoints under the base URL:

| Endpoint path | Description |
| --- | --- |
| `tile/{element}/{levelOfDetail}/{tileX}/{tileY}.{format}` | XYZ/Slippy-map tiles for use in web maps and map SDKs |
| `image/{element}/{levelOfDetail}.{format}` | Static stitched map images centered on a given location |
| `legend/{element}` | JSON legend describing the color scale for a given layer |

## Authentication

The Weather Maps API supports both anonymous and authenticated requests.

**Anonymous access:** You can use supported Weather Maps API endpoints without an API key or account registration. Visual Crossing manages usage by client IP address and applies fair-use limits.

**Authenticated access:** Registered users can include their API key in the request to access their account’s usage allowances and available features.

The API accepts either of the following query parameters:

-   `apikey` – Recommended parameter for authenticated requests.
-   `key` – Legacy alias, also supported.

If a request requires authentication or your account does not have access to the requested feature, the API may return `401 UNAUTHORIZED` or another appropriate error.

For working examples without authentication, see the [Free Weather API guide](https://www.visualcrossing.com/resources/documentation/weather-api/free-weather-api-no-key/).

## Supported elements and sources

Each map request targets a specific element (map layer) and optional data source/model.

-   `element` (path parameter) – map layer name such as `precipcomposite`, `temp` or `wind`
-   `source` (query parameter, optional) – source model identifier.
    -   If present, it is passed through to the backend as the forecast model name or other layer source.
    -   This allows you to request specific model variants where available such as radar only precipitation or a particular weather forecast model.
    -   When omitted, a default source will be chosen based on the element. This typically combines multiple sources to produce consistent, worldwide coverage for that map layer.

If an unknown element or source is requested, the API will return an error indicating that the requested layer cannot be generated.

Available elements

| **Element** | **Formats** | **Description** | **Update interval** |
| --- | --- | --- | --- |
| precipcomposite | webp | Precipitation intensity and precipitation type derived from radar and other available observational and forecast sources. | 10-15 minutes |
| preciprate | webp | Rate of liquid equivalent precipitation. | 10-15 minutes |
| preciptype | webp | Type of precipitation (rain, snow, mix etc) | 10-15 minutes |
| temp | webp | Temperature | 60 minutes |
| alerts | webp/geojson | Active weather alerts and warnings | 10-15 minutes |
| current | geojson | Current conditions for selection towns and cities | 10-15 minutes |
| wind | webp | Wind direction and speed | 60 minutes |
| windspeed | webp | Wind speed | 60 minutes |
| winddir | webp | Wind direction | 60 minutes |
| lightning | webp | Lightning strikes | 10 minutes |
| hail | webp | Hail size detections | 10 minutes |

## Tile endpoint – Slippy-map tiles

The `tile` endpoint returns map tiles in the standard XYZ scheme commonly used by web mapping libraries such as Leaflet, OpenLayers, or Mapbox GL. The tile size is fixed at 256×256 pixels.

### URL format

```
https://maps.visualcrossing.com/VisualCrossingWebServices/rest/api/v1/map/
  tile/{element}/{levelOfDetail}/{tileX}/{tileY}.{format}
  ?apikey=YOUR_API_KEY
  &time=latest
  &unitGroup=us
  &lang=en
```

Path parameters:

-   `element` (required)  
    Logical layer id, for example:
    -   `precipcomposite` – radar-based precipitation composite
    -   `temp` – temperature
-   `levelOfDetail` (required, integer)  
    Tile zoom level, equivalent to Z in XYZ tiling.
    -   Higher values show more detail and cover a smaller geographic area.
    -   Typically 0 (world) through 12–14 (regional/local), depending on layer and configuration.
-   `tileX` (required, integer)  
    X index of the tile in the XYZ tiling scheme.
-   `tileY` (required, integer)  
    Y index of the tile in the XYZ tiling scheme.
-   `format` (optional path parameter, default `webp`)  
    Tile format extension:
    -   `webp` – recommended for most use cases, best balance of size and quality
    -   `png` – lossless output (larger files)
    -   geojson – JSON format for including vector spatial data.

### Query parameters

-   `apikey` or `key` (required)  
    Your Visual Crossing Weather API key.
-   `time` (optional, default `latest`)  
    Requested valid time for the tile image. Supported values:
    -   `latest` – the most recent available analysis/forecast time.
    -   ISO-8601 UTC timestamp, for example `2026-05-26T12:50`.
    -   When `time=latest`, tiles are treated as evolving and do not include `Last-Modified`/`ETag` headers.
    -   When a specific timestamp is provided, the response includes caching headers so tiles can be cached as immutable.
-   `source` (optional)  
    Logical data source/model name (see “Supported elements and sources” above).
-   `unitGroup` (optional, default `us`)  
    Controls the unit system used to interpret legends and labels. Where possible, the tiles will not change colors or banding based on unit group.
    -   `us` – Fahrenheit, inches, mph
    -   `metric` – Celsius, mm, m/s
    -   `uk` – Celsius, mph, mm
    -   `base` – raw internal units where applicableFor tile imagery this often affects the legend and color bin thresholds rather than the raw pixel values.
-   `lang` (optional, default `en`)  
    Language code for any text used within the tile product (where applicable).

### Example – latest precipitation tile

Request a WebP precipitation composite tile for zoom level 6, tile (17,22), using the latest available time:

```
https://maps.visualcrossing.com/VisualCrossingWebServices/rest/api/v1/map/
  tile/precipcomposite/6/17/22.webp
  ?apikey=YOUR_API_KEY
  &time=latest
```

Request the same tile at a fixed UTC timestamp, suitable for long-term caching in CDNs and browsers:

```
https://maps.visualcrossing.com/VisualCrossingWebServices/rest/api/v1/map/
  tile/precipcomposite/6/17/22.webp
  ?apikey=YOUR_API_KEY
  &time=2026-05-26T12:50
```

For timestamped tiles, the response includes:

-   `Cache-Control` headers configured by the backend
-   `Last-Modified` based on the parsed `time` parameter
-   `ETag` combining element, zoom, tile coordinate, time, and format

## Image endpoint – stitched static maps

The `image` endpoint generates a static map image by composing the tiles for a given element, zoom level, center point, and output size. This is ideal for server-side reports, dashboards, emails, or anywhere you need a single image rather than interactive tiles.

### URL format

```
https://maps.visualcrossing.com/VisualCrossingWebServices/rest/api/v1/map/
  image/{element}/{levelOfDetail}.{format}
  ?apikey=YOUR_API_KEY
  &time=latest
  &width=800
  &height=600
  &centerLat=0
  &centerLon=0
```

Path parameters:

-   `element` (required)  
    Logical layer id, same as for the `tile` endpoint.
-   `levelOfDetail` (required, number)  
    Zoom level. Unlike the tile endpoint, this value is a floating-point number, allowing intermediate zoom levels between standard integer tile zooms.
    -   For example, `6.5` may be used to approximate a zoom level between 6 and 7.
-   `format` (optional, default `webp`)  
    Output image format (`webp`, `png`, or `jpg`).

### Query parameters

-   `apikey` or `key` (required)  
    Your Visual Crossing Weather API key.
-   `time`  
    Requested time for the image. Supported values:
    -   `latest`– the most recent available analysis/forecast time.
    -   ISO-8601 UTC timestamp, for example `2026-05-26T12:50`.
-   `source` (optional)  
    Logical source/model identifier for the underlying data.
-   `altitude` (optional, default `0`)  
    Vertical level/altitude for applicable products.
-   `width` (optional, default `800`)  
    Output image width in pixels.
-   `height` (optional, default `600`)  
    Output image height in pixels.
-   `centerLat` (optional, default `0`)  
    Latitude of the image center in decimal degrees (−90 to 90).
-   `centerLon` (optional, default `0`)  
    Longitude of the image center in decimal degrees (−180 to 180).

### Example – global precipitation image

Request a 1024×768 WebP image showing a regional precipitation composite centered over Europe:

```
https://maps.visualcrossing.com/VisualCrossingWebServices/rest/api/v1/map/
  image/precipcomposite/4.webp
  ?apikey=YOUR_API_KEY
  &time=latest
  &width=1024
  &height=768
  &centerLat=50
  &centerLon=10

```

### Example – high-zoom image for a city

Request a high-zoom surface temperature map centered over London:

```
https://maps.visualcrossing.com/VisualCrossingWebServices/rest/api/v1/map/
  image/temp-surface/10.0.png
  ?apikey=YOUR_API_KEY
  &time=2025-11-26T12:00Z
  &width=1200
  &height=800
  &centerLat=51.5074
  &centerLon=-0.1278
```

The `legend` endpoint returns a JSON legend describing the color bins, ranges, and labels used when rendering a given `element`. This is useful when:

-   You want to draw your own color bar in a UI
-   You need to correlate pixel colors with actual values
-   You are integrating Visual Crossing maps into a third-party visualization framework

### URL format

```
https://maps.visualcrossing.com/VisualCrossingWebServices/rest/api/v1/map/
  legend/{element}
  ?apikey=YOUR_API_KEY
  &unitGroup=metric
```

Path parameters:

-   `element` (required)  
    Logical layer id, for example `precipcomposite`, `temp`, etc.

### Query parameters

-   `apikey` or `key` (required)  
    Your Visual Crossing Weather API key.
-   `unitGroup` (optional, default `metric`)  
    Unit group for the legend thresholds: `metric`, `us`, `uk`, or `base`. Note that this may differ from the default `unitGroup` used in other APIs. Legend units should match the way you intend to describe or post-process the values.

### Typical legend response structure

```
{
  "element": "temp-surface",
  "unitGroup": "metric",
  "units": "C",
  "bins": [
    { "min": -40, "max": -30, "color": "#2b2bff", "label": "< -30" },
    { "min": -30, "max": -20, "color": "#4a4aff", "label": "-30 to -20" },
    { "min": -20, "max": -10, "color": "#6b6bff", "label": "-20 to -10" },
    { "min": -10, "max": 0,   "color": "#8c8cff", "label": "-10 to 0" },
    { "min": 0,   "max": 10,  "color": "#66c2a5", "label": "0 to 10" },
    { "min": 10,  "max": 20,  "color": "#fee08b", "label": "10 to 20" },
    { "min": 20,  "max": 30,  "color": "#f46d43", "label": "20 to 30" },
    { "min": 30,  "max": 40,  "color": "#d73027", "label": "> 30" }
  ]
}
```

Use the `color` field to match rendered tile colors and the `min`/`max` values to display numeric ranges in a legend bar. The actual fields and bin boundaries will vary by element.

Example – fetching a legend for Celsius temperatures:

```
https://maps.visualcrossing.com/VisualCrossingWebServices/rest/api/v1/map/legend/temp?apikey=YOUR_API_KEY&unitGroup=metric
```

## Using the Weather Maps API from a map library

Here is a minimal example of using the tile endpoint with Leaflet (JavaScript):

```
const apiKey = "YOUR_API_KEY";
const element = "precipcomposite";

const tilesUrlTemplate =
  "https://maps.visualcrossing.com/VisualCrossingWebServices/rest/api/v1/map/" +
  "tile/" + element + "/{z}/{x}/{y}.webp?apikey=" + apiKey + "&time=latest";

const map = L.map("map").setView([40, -100], 5);

L.tileLayer(tilesUrlTemplate, {
  attribution: "Weather data © Visual Crossing",
  tileSize: 256,
  maxZoom: 14
}).addTo(map);
```

You can then fetch the corresponding legend using the `legend` endpoint and display it alongside your map.

The Weather Maps API uses standard HTTP status codes:

-   `200 OK` – Request processed successfully; tile/image/legend returned.
-   `400 BAD_REQUEST` – Invalid combination of parameters or malformed request.
-   `401 UNAUTHORIZED` – Invalid API key, missing API key, or account not enabled for Maps API.
-   `404 NOT_FOUND` – Unknown element, unsupported endpoint path, or invalid tile coordinate.
-   `429 TOO_MANY_REQUESTS` – Account has exceeded its assigned limits (rate, concurrency, or usage).
-   `500 INTERNAL_SERVER_ERROR` – General error processing the request.

When possible, the response body will include additional diagnostic information describing the cause of the error. For general guidance on debugging API issues, refer to the main Weather API documentation and troubleshooting guides.

## Notes and best practices

-   For interactive maps, prefer `time=latest` to always show the most recent analysis or forecast. For reproducible outputs or caching, use explicit UTC timestamps.
-   Use the `legend` endpoint rather than relying on hard-coded color scales. This ensures consistency across elements and future updates.

## Access, pricing, and radar data sources

### Is the Weather Maps API included with my Weather API account?

The Weather Maps API uses the same account and API key as the [Visual Crossing Weather API](https://www.visualcrossing.com/weather-api/). You do not need to create a separate account, purchase a separate subscription, or obtain an additional API key.

Weather Maps API usage is included within your existing Visual Crossing subscription and is counted according to the usage rules and pricing of your current plan.

### How are Weather Maps API requests billed?

Weather Maps API usage is based on the number of map tiles required to generate the requested map.

On the Metered plan, each map tile costs $0.0001. This applies whether tiles are requested individually for an interactive map or combined by the API to produce a stitched static image.

On non-metered plans, plans include a separate quantity of map requests per month. For more details, see our [pricing and plan information](https://www.visualcrossing.com/weather-data-pricing/).

### How are stitched static image requests billed?

A stitched static image is not necessarily billed as a single request. Its usage depends on the image dimensions, zoom level, geographic coverage, and the number of underlying tiles needed to generate it.

For example:

-   A single tile request counts as one map tile.
-   An interactive map normally loads multiple tiles, with each returned tile counted separately.
-   A static radar image may require several underlying tiles to cover the requested image area.
-   Larger images and wider geographic areas generally require more tiles.
-   Each frame in an animated radar display requires its own set of tiles.

Applications can reduce repeated usage by caching map results for fixed timestamps. Requests for a specific historical or forecast time can generally be reused, while requests using `time=latest` should be refreshed only as often as the application requires.

## What sources of data are used for the weather maps?

The weather maps combine many different data sources into a single set of rendered layers. Rather than relying on one feed, the map automatically selects the best available source for the requested location, time, and map configuration, blending live observations, satellite data, and forecast models so that coverage is as complete and current as possible worldwide.

### The precipitation composite layer

The precipcomposite layer merges precipitation intensity and precipitation-type information into a single rendered weather layer. It is the flagship layer and draws on the widest range of sources:

-   Within the contiguous United States, the radar portion is derived from NOAA weather radar products, including the NEXRAD radar network and NOAA’s Multi-Radar/Multi-Sensor System (MRMS).
-   Outside the United States, precipitation coverage may come from regional radar networks, satellite-derived observations, forecast models, or a combination of available sources.

### Radar and remote-sensing observations

For near-real-time “what’s happening right now” coverage, the maps use radar and satellite observations:

-   MRMS / NEXRAD — high-resolution radar mosaic across the contiguous US, updated every few minutes. This also feeds hail size, hail probability, and lightning products.
-   DWD radar — Deutscher Wetterdienst radar covering Germany and central Europe.
-   UK Met Office radar — radar coverage over the United Kingdom.
-   OPERA — the pan-European radar composite covering most of Europe.
-   Satellite — geostationary satellite observations (e.g. GOES for the Americas and MTG-LI for lightning) extend coverage over oceans and regions without ground radar.

### Forecast models

Where observations are unavailable, or when looking into the future, the maps fall back to numerical weather-prediction models. Coverage is geographically bounded to each model’s domain:

-   Global models — GFS, ECMWF, ECMWF AIFS, AI GFS, ICON Global, UK Met Office Global, GDPS (Canada), and the GFES ensemble.
-   North America — HRRR, HRRR sub-hourly, NAM, RRFS, REFS, NDFD, HRDPS, RDPS, and the US Blended product.
-   Europe — ICON-EU, ICON-D2, AROME, ARPEGE, UK Met UKV.
-   Air quality — CAMS (global) and NDGD (US) provide pollutant layers such as PM2.5, PM10, ozone, NO₂, SO₂, and CO.

Each model supplies its own set of layers — commonly precipitation composite, precipitation rate, precipitation type, temperature, wind, and wind speed — at that model’s native time resolution.

### Weather alerts

A separate alerts layer overlays official government-issued watches, warnings, and advisories on top of the weather data.

### How the source is chosen

-   When the source=radar parameter is used, the API requests radar-derived coverage wherever radar data is available, giving the most accurate depiction of current, observed conditions.
-   When the source parameter is omitted, the API can select from all available radar, satellite, and model data to provide the broadest possible geographic coverage — using observations where they exist and seamlessly filling gaps (over oceans, remote regions, or into the future) with forecast-model data.

Because of this, the exact source behind any point on the map depends on where you’re looking, when (past, present, or forecast), and which model or configuration is selected.

## Questions or need help?

Our [AI Code generato](https://www.visualcrossing.com/support/weather-api-ai-code-generator/)r provides real-time, 24×7 access to code generation and other questions related to the Weather API usage.

If you have a additional questions, please post on our [actively monitored forum](https://support.visualcrossing.com/hc/en-us/community/topics/360001293151-Weather) for the fastest replies. You can also contact us via our [support site](https://www.visualcrossing.com/support/)

---
title: "Historical Forecast API"
date: "2026-11-20T11:03:03+07:00"
source: "https://www.visualcrossing.com/resources/documentation/weather-api/historical-forecast-api/"
---

# Page Structure Map
```text
- Overview
- Retrieval Modes
  - By Run (`byrun`)
    - How to interpret this
    - Example
    - Key idea
  - By Target (`bytarget`)
    - How to interpret this:
    - Example:
    - Key idea:
- Base URL
- Endpoints
- Request Parameters
  - Path Parameters (required)
  - Core Query Parameters
  - Options
  - Additional Parameters
  - Additional Fields for Historical Forecasts
    - How they work together
    - Why this matters
- Example Requests
  - By Run (JSON)
  - By Target (CSV)
  - Using V2 Forecast Data
- POST Requests
- Response Format
- HTTP Response Codes
- Rate Limits and concurrency
- Support
```
---

## Overview

The Historical Weather Forecast API is a new endpoint, that provides access to weather forecasts as they were generated in the past.

Accessing historical weather forecasts allows you to:

-   Understand what weather conditions were expected at the time decisions were made
-   Analyze how forecasted weather influenced operational or business outcomes
-   Reconstruct the information available to systems, teams, or customers at a specific point in time
-   Support modeling and analytics that depend on forecast inputs rather than observed conditions

These end points use the same historical forecast architecture as the [Timeline Weather API](https://www.visualcrossing.com/resources/documentation/weather-api/timeline-weather-api/) `forecastBasisDate` and `forecastBasisDay` parameters but make retrieving historical forecast data even easier.

The response format is fully compatible with the Timeline Weather API, enabling reuse of existing integrations. Where possible, API parameters are provided in the same way as the Timeline Weather API for straightforward integration.

A subscription to the [Historical Forecast Addon](https://www.visualcrossing.com/weather-data-editions/) subscription is required for the Historical Forecast API.

## Retrieval Modes

The API supports two retrieval modes depending on how you want to query historical forecasts:

### By Run (`byrun`)

In *byrun* mode, each forecast is grouped by the date it was generated. Each run produces a forward-looking forecast covering multiple days.

For example, consider the following data:

```
rundatetime,forecastday,datetime,tempmax,tempmin2025-01-01,0,2025-01-01,44.7,31.92025-01-01,1,2025-01-02,37.7,27.22025-01-01,2,2025-01-03,41.8,26...2025-01-02,0,2025-01-02,39.3,29.62025-01-02,1,2025-01-03,41.3,272025-01-02,2,2025-01-04,31.4,22...
```

*https://weather.visualcrossing.com/VisualCrossingWebServices/rest/api/v1/historicalforecast/Herndon,VA/byrun/2025-01-01/2025-01-02?unitGroup=us&include=days&key=YOUR\_API\_KEY&contentType=csv&elements=name,rundatetime,forecastday,datetime,tempmax,tempmin*

Each group of rows shares the same **rundatetime**, which represents when the forecast was issued.

-   **rundatetime** – The date the forecast was generated
-   **forecastday** – The lead time (0 = same day, 1 = next day, etc.)
-   **datetime** – The actual date being forecast

#### How to interpret this

-   The forecast run on 2025-01-01 includes predictions for January 1 through January 6
-   The forecast run on 2025-01-02 includes updated predictions for January 2 through January 7

This means the same calendar date (for example, January 3) can appear multiple times—once for each forecast run that predicted it.

#### Example

-   January 3 was forecast as:
    -   41.8°F when predicted on January 1 (2 days ahead)
    -   41.3°F when predicted on January 2 (1 day ahead)

#### Key idea

Each forecast run represents the full set of expectations available at that point in time.  
This allows you to reconstruct exactly what was known on any given day and how expectations evolved as new forecasts were issued.

### By Target (`bytarget`)

In bytarget mode, forecasts are grouped by the date being predicted rather than when the forecast was issued.

Each target date appears multiple times—once for each forecast run that predicted it at different lead times.

For example, consider the following data:

```
rundatetime,forecastday,datetime,tempmax,tempmin
2024-12-27,5,2025-01-01,47.6,40.9
2024-12-28,4,2025-01-01,50.3,36.9
2024-12-29,3,2025-01-01,48.1,37.1
2024-12-30,2,2025-01-01,48.6,36
2024-12-31,1,2025-01-01,47.7,36
...
2024-12-28,5,2025-01-02,40,29.6
2024-12-29,4,2025-01-02,42,29.7
2024-12-30,3,2025-01-02,40.4,29.2
2024-12-31,2,2025-01-02,39.1,30.8
2025-01-01,1,2025-01-02,37.7,27.2

https://weather.visualcrossing.com/VisualCrossingWebServices/rest/api/v1/historicalforecast/Herndon,VA/bytarget/2025-01-01/2025-01-02?unitGroup=us&include=days&key=YOUR_API_KEY&contentType=csv&elements=name,rundatetime,forecastday,datetime,tempmax,tempmin
```

Each group of rows shares the same **datetime**, which represents the day being forecast.

-   **rundatetime** – The date the forecast was generated
-   **forecastday** – The lead time (how many days ahead the forecast was made)
-   **datetime** – The actual date being predicted

#### How to interpret this:

-   For January 1, forecasts were made over multiple days leading up to it
-   Each row shows what the forecast predicted for January 1 at a specific lead time
-   As the forecast gets closer to the target date, the lead time decreases and the prediction is updated

#### Example:

For January 1, the forecast evolved as follows:

-   5 days ahead (Dec 27): 47.6°F
-   4 days ahead (Dec 28): 50.3°F
-   3 days ahead (Dec 29): 48.1°F
-   2 days ahead (Dec 30): 48.6°F
-   1 day ahead (Dec 31): 47.7°F

#### Key idea:

Each target date shows how expectations changed over time as new forecasts were issued.  
This makes it possible to analyze how a specific day was predicted in advance and how those predictions evolved leading up to that date.

---

## Base URL

```
https://weather.visualcrossing.com/VisualCrossingWebServices/rest/api/v1/historicalforecast/
```

## Endpoints

-   ``/historicalforecast/[location]/byrun/[rundatestart]/[`run`dateend]``
-   ``/historicalforecast/[location]/bytarget/[targetdatestart]/[`target`dateend]``

---

## Request Parameters

### Path Parameters (required)

-   **location** – Address, latitude/longitude, postal code, or weather station ID
-   **datestart** – Start date (`yyyy-MM-dd`)
-   **dateend** – End date (`yyyy-MM-dd`, inclusive)

### Core Query Parameters

-   **key** (required) – Your API key
-   **unitGroup** – `us`, `metric`, `uk`, `base`
-   **contentType** – `json` (default) or `csv`
-   **include** – `days`, `hours`, `current`, `alerts`
-   **elements** – Filter specific weather fields
-   **forecastdays** – Number of forecast days returned per run (default: 5)
-   **forecastDataset** – Specific model (e.g., `gfs`, `hrrr`, `iconeu`)

### Options

-   `nonulls` – Remove null values
-   `noheaders` – Remove CSV headers
-   `usev2forecast` – Use enhanced V2 forecast data where available

### Additional Parameters

All standard Timeline API parameters are supported, including:

-   `lang`
-   `timezone`
-   `iconSet`
-   `maxStations`, `maxDistance`, `elevationDifference`
-   Degree day and solar calculation parameters

See the [Timeline API documentation](https://www.visualcrossing.com/resources/documentation/weather-api/timeline-weather-api/) for full details.

### Additional Fields for Historical Forecasts

The Historical Forecast API introduces two additional fields that describe how each forecast was generated:

-   rundatetime – The date the forecast model was run
-   forecastday – The lead time of the forecast (how many days ahead the prediction was made)

These fields provide context about when the forecast was created and how far into the future it was predicting.

#### How they work together

-   forecastday = 0 → Forecast for the same day as the run
-   forecastday = 1 → Forecast made one day in advance
-   forecastday = 5 → Forecast made five days in advance

For example:

-   A record with rundatetime = 2024-12-27 and forecastday = 5 corresponds to a prediction for 2025-01-01
-   A record with rundatetime = 2024-12-31 and forecastday = 1 also corresponds to a prediction for 2025-01-01

Both rows describe forecasts for the same target date, but from different points in time.

#### Why this matters

These fields allow you to:

-   Track how forecasts evolve as the target date approaches
-   Compare short-range vs long-range predictions
-   Align forecasts with business decisions based on when they were made

Together, rundatetime and forecastday define the relationship between when a forecast was issued and what date it was predicting.

## Example Requests

### By Run (JSON)

```
https://weather.visualcrossing.com/VisualCrossingWebServices/rest/api/v1/historicalforecast/Herndon,VA/byrun/2025-01-01/2025-01-15?unitGroup=us&include=days,hours&key=YOUR_API_KEY
```

### By Target (CSV)

```
https://weather.visualcrossing.com/VisualCrossingWebServices/rest/api/v1/historicalforecast/Herndon,VA/bytarget/2020-01-01/2020-01-15?contentType=csv&include=days,hours&key=YOUR_API_KEY
```

### Using V2 Forecast Data

```
https://weather.visualcrossing.com/VisualCrossingWebServices/rest/api/v1/historicalforecast/38.9697,-77.385/byrun/2025-01-01/2025-01-15?options=usev2forecast&key=YOUR_API_KEY
```

---

## POST Requests

POST requests are supported for large or structured queries. Path parameters remain in the URL.

```
POST /rest/api/v1/historicalforecast/Herndon,VA/byrun/2025-01-01/2025-01-15
Content-Type: application/json

{
  "key": "YOUR_API_KEY",
  "unitGroup": "us",
  "include": "days,hours",
  "forecastdays": 5
}
```

## Response Format

Responses follow the same structure as the Timeline Weather API.

-   **Location metadata** – coordinates, timezone, resolved address
-   **days\[\]** – daily forecast records
-   **hours\[\]** – hourly forecast records (if requested)

Each record includes standard weather elements such as temperature, precipitation, wind, pressure, and conditions.

The `source` field is typically `"histfcst"`, indicating historical forecast data.

## HTTP Response Codes

-   **200** – Success
-   **400** – Invalid request
-   **401** – Unauthorized (invalid key or permissions)
-   **429** – Rate limit exceeded
-   **500** – Server error

## Rate Limits and concurrency

This API integrates data from multiple forecast models and is designed to operate with a single concurrent request per account. Standard plan concurrency limits do not apply to this endpoint.

If you require bulk historical forecast data, please [contact support](https://www.visualcrossing.com/support/) to discuss access to our upcoming bulk download capability.

## Support

Use the [AI Code Generator](https://www.visualcrossing.com/support/weather-api-ai-code-generator/) for request examples and integration help.

For support, visit [Visual Crossing Support](https://www.visualcrossing.com/support/).

---
title: "Air Quality Elements in the Weather API"
date: "2026-11-20T11:03:11+07:00"
source: "https://www.visualcrossing.com/resources/documentation/weather-api/air-quality-elements-in-the-weather-api/"
---

# Page Structure Map
```text
- API Request Format
- Available Air Quality Elements
- Questions or need help?
```
---

The [Timeline Weather API](https://www.visualcrossing.com/weather-api) offers optional air quality index elements. This article describes how to include them with your API request. Air Quality Elements are currently available globally with limited history and five day forecast (hourly or daily).

## API Request Format

All weather alerts API requests follow the same form as [a regular Timeline Weather API request](https://www.visualcrossing.com/resources/documentation/weather-api/timeline-weather-api/). Air quality elements are included using the elements parameter. In this following example, only the datetime and the Air quality elements are requested:

https://weather.visualcrossing.com/VisualCrossingWebServices/rest/services/timeline/New York City,NY?unitGroup=metric&key=YOUR\_API\_KEY&contentType=json**&elements=datetime,pm1,pm2p5,pm10,o3,no2,so2,co,aqius,aqieur**

You may also use the + prefix to add air quality elements to the default set of elements. In this example, the US Air Quality Index is added to the list of default elements.

https://weather.visualcrossing.com/VisualCrossingWebServices/rest/services/timeline/New York City,NY?unitGroup=metric&key=YOUR\_API\_KEY&contentType=json**&elements=+aqius**

Note that in most systems, the ‘+’ character must be URL encoded:

https://weather.visualcrossing.com/VisualCrossingWebServices/rest/services/timeline/New York City,NY?unitGroup=metric&key=YOUR\_API\_KEY&contentType=json**&elements=%2Baqius**

## Available Air Quality Elements

**aqius – United States Air Quality Index** – the US EPA’s calculation for the Air Quality which combines multiple aspects of the atmospheric chemistry to create a single air quality score. This is an integer ranging from 0 (very low air pollution) to 300 or more. Values above 100 indicate a polluted air (see [https://www.airnow.gov/aqi/aqi-basics/](https://www.airnow.gov/aqi/aqi-basics/) for more information).

**aqieur – European Air Quality Index** – the European calculation for air quality. This is a number ranging from 1 (very low pollution) to 6 (extremely high pollution). For more information, see [https://ecmwf-projects.github.io/copernicus-training-cams/proc-aq-index.html](https://ecmwf-projects.github.io/copernicus-training-cams/proc-aq-index.html)

**pm1 – Particulate matter <1 micrometers diameter** – any solid and aerosols less than one micron in size. Particulate matter has many sources including combustion products, dust, wild fires, factories etc. Values are in

**pm2p5 – Particulate matter <2.5 micrometers diameter** – any solid and aerosols less than 2.5 micron in size.

**pm10 – Particulate matter <10 micrometers diameter** – any solid and aerosols less than ten micron in size.

**o3 – ground level ozone** – The amount of ozone near the surface. Values are in microns meter cubed.

**no2 – nitrogen dioxide –** The amount of nitrogen dioxide. Values are in microns meter cubed.

**so2 – sulphur dioxide –** The amount of sulphur dioxide. Values are in microns meter cubed.

**co – carbon monoxide –** The amount of carbon monoxide. Values are in microns meter cubed.

## Questions or need help?

If you have a question or need help, please post on our [actively monitored forum](https://support.visualcrossing.com/hc/en-us/community/topics/360001293151-Weather) for the fastest replies. You can also contact us via our [Support Team](https://www.visualcrossing.com/support/).
