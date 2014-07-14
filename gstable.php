<?php
/*

	GameSalad Tables Library (PHP)
	
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

	Parse a table from JSON:
	
	$gstable = new GSTable;
	$gstable->parseJSON($_POST["params"]);
	
	Create a table from scratch:
	$gstable = new GSTable(
		[
			["column1", GSTable::TYPE_INTEGER],
			["column2", GSTable::TYPE_BOOLEAN],
			["column3", GSTable::TYPE_STRING]
		],  # Column def
		["row1", "row2", "row3"] # Row labels
	);

	Access Rows:
	$row_array = $gstable[0];
	$row_array = $gstable.getByRowLabel("row1");
	
	Update Rows By Index:
	$gstable[1] = [1, true, "All The Things!"];
	$gstable.setByRowLabel("row2", [1, true, "All The Things!"]);
	
	Update Row Label After The Fact:
	$gstable.setRowLabel(1, "bowlboy_count");
	
	Grab Data By Column:
	$column_array = $gstable.getColumn(1);
	$column_array = $gstable.getColumnByLabel("column2");

	Validate:
	$gstable[1] = ["ab", false, "100"] # Throws Exception because ab isn't an integer.

*/
class GSTable implements ArrayAccess {
	
	const TYPE_STRING = 1;
	const TYPE_INTEGER = 2;
	const TYPE_REAL = 3;
	const TYPE_BOOLEAN = 4;
	const TYPE_ANGLE = 5;
	
	function __construct($columns = null, $rowNames = null) {
		$this->column_names = [];
		$this->column_types = [];
		if (!is_null($columns)) {
			$this->column_count = count($columns);
			foreach($columns as $column_spec) {
				$this->column_names []= $column_spec[0];
				$this->column_types []= $column_spec[1];
			}			
		} else {
			$this->column_count = 0;
		}
		
		$this->row_labels = [];
		$this->rows = [];
		if (!is_null($rowNames)) {
			$this->row_count = count($rowNames);
			for($i = 0; $i < count($rowNames); $i++) {
				$this->row_labels[$rowNames[$i]] = $i;
			}
		} else {
			$this->row_count = 0;
		}
	}
	
	// Helper function that parsers the header meta data
	function parse_header($header_info) {
		foreach($header_info as $property) {
			$value = $property["Value"];
			switch ($property["Name"]) {
				case "rowCount":
					$this->row_count = $value;
					break;
				case "columnCount":
					$this->column_count = $value;
					break;
				default:
					$property_info = split("-", $property["Name"]);
					$idx = intval($property_info[1]) - 1;
					switch ($property_info[2]) {
						case "type":
							$this->column_types[$idx] = $value;
							break;
						case "name":
							$this->column_names[$idx] = $value;
							break;
					}
			}
		}
	}

	// Validates a new row to make sure it conforms to the column data type definitions.
	function validateRow($row) {
		for ($i = 0; $i < count($row); $i++) {
			switch ($this->column_types[$i]) {				
				case self::TYPE_INTEGER:
					if (!is_integer($row[$i])) {
						throw new Exception("Row value '" . $row[$i] . "' at index " . $i . " is not a valid integer.");
					}
					break;
				case self::TYPE_REAL:
					if (!is_float($row[$i])) {
						throw new Exception("Row value '" . $row[$i] . "' at index " . $i . " is not a valid real number.");
					}
					break;
				case self::TYPE_ANGLE:
					if (!(is_float($row[$i]) || is_integer($row[$i])) || !($row[$i] >= 0 && $row[$i] <= 360)) {
						throw new Exception("Row value '" . $row[$i] . "' at index " . $i . " is not a valid angle number (between 0 and 360).");
					}
					break;
			}
		}
	}
	
	// Funtions that allow you to access the data like an array.
	function offsetGet($offset) {
		return $this->rows[$offset];
	}

	function offsetExists($offset) {
		return !is_null($this->rows[$offset]);
	}
	
 	function offsetSet ($offset , $value) {
		$this->validateRow($value);
		$this->rows[$offset] = $value;
		$this->row_count = max(array_keys($this->rows)) + 1;
	}

	function offsetUnset($offest) {
		$this->rows[$offset] = null;
		$this->row_count = max(array_keys($this->rows)) + 1;
	}

	function &getByRowLabel($rowName) {
		$idx = $this->row_labels[$rowName];
		if (!array_key_exists($idx, $this->rows)) {
			$this->rows[$idx] = [];
		}
		return $this->rows[$idx];
	}

	function push($rowData) {
		$this->validateRow($rowData);
		$this->rows []= $rowData;
		$this->row_count ++;
	}

	function setByRowLabel($rowName, $rowData) {
		$this->validateRow($rowData);
		
		$idx = $this->row_labels[$rowName];
		if (!array_key_exists($idx, $this->rows)) {
			$this->rows[$idx] = [];
		}
		$this->rows[$idx] = $rowData;
	}

	function setRowLabel($row, $rowName) {
		$idx = $row - 1;
		$this->row_labels[$rowName] = $idx;
		$this->row_count = max(array_keys($this->rows)) + 1;
	}

	// Row Count
	function count() {
		return max(array_keys($this->rows)) + 1;
	}

	function getColumn($idx) {
		$column = [];
		foreach($this->rows as $row) {
			$column []= $row[$idx];
		}
		return $column;
	}

	// Grab data for a column (rather than a row)
	function getColumnByLabel($name) {
		$column = [];
		$idx = array_search($name, $this->column_names);
		foreach($this->rows as $row) {
			$column []= $row[$idx];
		}
		return $column;
	}

	function toJSON() {
		$json_obj = [
			"Children" => [],
			"Name" => "",
			"Properties" => []
		];
		
		// Form Header
		$header_info = [
			[
				"Name" => "rowCount",
				"Value" => $this->row_count
			],
			[
				"Name" => "columnCount",
				"Value" => $this->column_count
			]
		];
		
		for ($i = 0; $i < count($this->column_types); $i++) {
			$header_info []= [
				"Name" => implode("-", ["0", $i + 1, "name"]),
				"Value" => $this->column_names[$i]
			];
			$header_info []= [
				"Name" => implode("-", ["0", $i + 1, "type"]),
				"Value" => $this->column_types[$i]
			];
		}
		
		$json_obj["Children"] []= [
			"Children" => [],
			"Name" => $this->table_name . "_headers",
			"Properties" => $header_info
		];
		
		$rows = [];
		$row_labels = array_flip($this->row_labels);
		for ($i = 0; $i < $this->row_count; $i++) {
			$new_row = $row_labels[$i] . "|";			
			for ($c = 0; $c < $this->column_count; $c++) {
				if ($this->rows[$i]) {
					if ($this->column_types[$c] == self::TYPE_BOOLEAN) {
						$new_row .= $this->rows[$i][$c] ? "True" : "False";
					} else if ($this->column_types[$c] != self::TYPE_STRING) {
						$new_row .= empty($this->rows[$i][$c]) ? 0 : $this->rows[$i][$c];
					} else {
						$new_row .= str_replace("|", "\\|", str_replace("\\", "\\\\", $this->rows[$i][$c]));
					}
				}
				$new_row .= "|";
			}
			
			$rows []= [
				"Name" => strval($i + 1),
				"Value" => $new_row
			];
		}
		
		$json_obj["Children"] []= [
			"Children" => [],
			"Name" => $this->table_name,
			"Properties" => $rows
		];
		
		return json_encode($json_obj);
	}

	function parseRow($row_str) {
		$current_col = "";
		$row = [];
		for ($i = 0; $i < strlen($row_str); $i++) {
			if ($row_str[$i] == "|") {
				array_push($row, $current_col);
				$current_col = "";
				$i++;
			} elseif ($row_str[$i] == "\\") {
				$i++;
			}
			$current_col .= $row_str[$i];
		}
		return $row;
	}

	// Parse the json into this object (maybe turn this into an initializer at some point?)
	function parseJSON($json_string) {

		$this->column_names = [];
		$this->column_types = [];
		$this->row_labels = [];
		$this->rows = [];
		$table_info = json_decode($json_string, true);
		foreach($table_info['Children'] as $row) {
			$header_idx = strpos($row["Name"], "_headers");
			if ($header_idx > 0) {
				$this->table_name = substr($row["Name"], 0, $header_idx);
				$this->parse_header($row["Properties"]);
			} else {
				foreach($row["Properties"] as $row_info) {
					// Need to put in a smarter parser that parses escaped |
					$parsed_row = $this->parseRow($row_info["Value"]);
					
					$row_idx = intval($row_info["Name"]) - 1;
					$this->row_labels[$parsed_row[0]] = $row_idx;
					$row_data = [];

					for ($i = 1; $i < count($parsed_row); $i++) {
						$idx = $i - 1;
						switch ($this->column_types[$idx]) {
							case self::TYPE_BOOLEAN:
								$row_data[$idx] = $parsed_row[$i] == "True";
								break;
							case self::TYPE_INTEGER:
								$row_data[$idx] = intval($parsed_row[$i]);
								break;
							case self::TYPE_REAL:
								$row_data[$idx] = floatval($parsed_row[$i]);
								break;
							case self::TYPE_ANGLE:
								$row_data[$idx] = floatval($parsed_row[$i]);
								break;
							default:
								$row_data[$idx] = $parsed_row[$i];
						}
					}
					$this->rows[$row_idx] = $row_data;
				}
			}
		}
	}
}
?>