# GSTable PHP

The GSTable class will allow you to parse table data posted from a [GameSalad](http://gamesalad.com) app and
create the correct JSON structure for table data requested from a GameSalad app.

The class provides a more natural interface to the table data.

# Usage #

## Parse a table from JSON: ##

    $gstable = new GSTable;
    $gstable->parseJSON($_POST["params"]);
	
## Create a table from scratch: ##
    
    $gstable = new GSTable(
        [
            ["column1", GSTable::TYPE_INTEGER],
            ["column2", GSTable::TYPE_BOOLEAN],
            ["column3", GSTable::TYPE_STRING]
        ],  # Column def
        ["row1", "row2", "row3"] # Row labels
    );

## Access Rows: ##

    $row_array = $gstable[0];
    $row_array = $gstable.getByRowLabel("row1");
	
## Update Rows By Index: ##

    $gstable[1] = [1, true, "All The Things!"];
    $gstable.setByRowLabel("row2", [1, true, "All The Things!"]);
	
## Update Row Label After The Fact: ##
    
    $gstable.setRowLabel(1, "bowlboy_count");
	
## Grab Data By Column: ##

    $column_array = $gstable.getColumn(1);
    $column_array = $gstable.getColumnByLabel("column2");

## Validate: ##

    $gstable[1] = ["ab", false, "100"] # Throws Exception because ab isn't an integer.


# Tic-Tac-Toe sample #

Place the php files on a server under a directory (we developed this code using the local Apache server on OS X).

Make sure gstable.php is in the same directory as the tic-tac-toe files.

Adjust the URL to match where the files are loading...

For instance in development I put this under my local http share that's accessed via http://127.0.0.1/~ttran.
Since I got a lot going on I put it in a tictactoe directory so to get to this app the Server URL is:

http://127.0.0.1/~ttran/tictactoe

Before you start you should set the "Game Server" attributein the tictactoe project to your Server's URL.

You will also need to run the *.sql scripts in a database.


# License #

The MIT License (MIT)

Copyright (c) 2014 GameSalad, Inc

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
