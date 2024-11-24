# War Thunder Sensor Database
The War Thunder Sensor Database is an application built atop PHPDesktop, which allows a user to view and assess information about sensors within War Thunder by parsing the data in the datamined assets, and computing various bits of information such as RWR threat tables, sensor sector coverage, among many more things.

Thanks to Gszabi, Oshida, FlareFlo, and many more who's datamine work and assistance in understanding the data has helped make this possible.

## Table of Contents

 1. [Downloading the application](#downloading-the-application)
 1. [Launching the application](#launching-the-application)
 1. [Updating the application](#updating-the-application)
 1. [Updating the database](#updating-the-database)
 1. [Is there a website version](#is-there-a-website-version)
 1. [Contact](#contact)

## Downloading the application
1. First you will need to download the latest version of PHP Desktop Chrome at the following link https://github.com/cztomczak/phpdesktop?tab=readme-ov-file#downloads.
1. Once you have this downloaded, extract the contents somewhere you would like to work.
1. Delete the WWW folder and the php-desktop.exe files (as they are no longer required)
1. You can then either clone the git repo to that folder location, or copy the files manually - replacing everything in the extracted phpdesktop files with the files from the repo.

## Launching the application
To launch the application, run the "sensor-database.exe" file in the main folder. This will launch a local PHP server using PHP Desktop, and open up the main page of the Database.

## Updating the application
Currently there is no way to update the application through the application. You will need to download the latest version from the git repository.

## Updating the database
The application has been built with future additions in sensors and units to the War Thunder client in mind. To that end, there is a page you can access by clicking on the "update database" link at the bottom of the main screen. On this page, the application will check the last version of the files you loaded, with the latest version of the datamined War Thunder client in Gszabi's repository. If there is a discrepency, a button will appear to allow you to update the database. When clicking this button, a download of the relevant datamined .blk files will be triggered to the www/data_raw folder from the War Thunder Open Source Foundation API. The application will then read these files, and re-build the database. After which these files will then be removed. Overall the update process for both Sensors and Units is around 400MB total.

## Is there a website version
Currently there is not a website version of the application available that does not require a download. In the future this may change once the toolset is more fleshed out and if it proves viable/useful to do.

## Contact
If you have any issues with the application, or suggestions for improvement, please reach out to me on the Snail Mine discord.

## Related Links
[Gszabi99 Github Repo](https://github.com/gszabi99/War-Thunder-Datamine)
[War Thunder Open Source Foundation](https://github.com/Warthunder-Open-Source-Foundation)
