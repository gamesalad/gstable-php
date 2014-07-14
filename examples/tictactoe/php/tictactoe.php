<?php
	define ("WIN", serialize ([
		[0, 1, 2],
		[3, 4, 5],
		[6, 7, 8],
		[0, 3, 6],
		[1, 4, 7],
		[2, 5, 8],
		[0, 4, 8],
		[2, 4, 6]
	]));

	class TicTacToe {
	
		function __construct($game_id, $player_id = null) {
			$this->db = new mysqli("127.0.0.1", "root", "", "tictactoe");
			if (mysqli_connect_errno()) {
				printf("Connection failed: %s\n", mysqli_connect_error());
				exit();
			}
			

			if (is_null($game_id)) {
				# Create a new row in the "games" table
				# Grab the ID for the row.
				# Set up game like below
			} else {
				# Grab game from db by ID
				$this->game_id = $game_id;
				$query = sprintf("select id as game_id, player_x, player_o, winner, created_at from games where id = %s", intval($game_id));
				$res = $this->db->query($query);
				if ($res === false) {
					$err = sprintf("Invalid query: %s\nWhole query: %s\n", $db->error, $query);
					throw new Exception($err);
				}
			}
			

			$row = $res->fetch_array(MYSQLI_ASSOC);
			$this->player_x = intval($row["player_x"]);
			$this->player_o = intval($row["player_o"]);
			$this->winner = intval($row["winner"]);
			$this->created_at = $row["created_at"];
		}

		static function active_games($player_id) {
			$db = new mysqli("127.0.0.1", "root", "", "tictactoe");
			$query = sprintf("select id as game_id, player_x, player_y, winner, created_at from games where player_x is null or player_y is null or player_x = %s or player_y = %s", $player_id, $player_id);
			$res = $db->query($query);
			if ($res === false) {
				$err = sprintf("Invalid query: %s\nWhole query: %s\n", $db->error, $query);
				throw new Exception($err);
			}
			
			$games = [];
			while ($row = $res->fetch_array(MYSQLI_ASSOC)) {
				$games []= $row;
			}
			return $games;
		}

		function load_game() {
			$query = "SELECT user_id, position FROM moves WHERE game_id = " . intval($this->game_id) . " order by created_at;";
			$res = $this->db->query($query);
			if ($res === false) {
				$err = sprintf("Invalid query: %s\nWhole query: %s\n", $this->db->error, $query);
				throw new Exception($err);
			}
			
			$this->game = [];
			$this->move_count = 0;
			while ($row = $res->fetch_array(MYSQLI_ASSOC)) {
				$this->game[$row["position"]]= $row["user_id"];
				$this->last_player = $row["user_id"];
				$this->move_count++;
			}			
		}

		function updateGame($grid) {
			error_log("Updating game with " . json_encode($grid));
			$this->load_game();
			$this->move_count = 0;
			for($i = 0; $i < 9; $i++) {
				if ($grid[$i] == 0) {
					$this->move_count++;
				}
				
				if ($grid[$i] != $this->game[$i] && $this->game[$i] != 0) {
					throw new Exception("Cheater!");
				}
				
				if ($this->game[$i] == 0 && $grid[$i] != 0) {
					$insert_move = sprintf(
						"INSERT INTO moves (game_id, user_id, position) VALUES (%s, %s, %s)", 
						intval($this->game_id), 
						intval($grid[$i]),
						$i
					);
					$rest = $this->db->query($insert_move);
					if ($res === false) {
						$err = sprintf("Invalid query: %s\nWhole query: %s\n", $this->db->error, $query);
						throw new Exception($err);
					}
				}
			}
		}

		function move($row, $col, $player_id) {
			$this->load_game();
			
			$new_move = ($row * $col) - 1;

			if (end($this->game) == $player_id) {
				throw new Exception("Not your turn");
			}

			if ($new_move > 8 || array_key_exists($new_move, $this->game)) {
				throw new Exception("Invalid Move");
			}

			$insert_move = sprintf(
				"INSERT INTO moves (game_id, user_id, position) VALUES (%s, %s, %s)", 
				intval($this->game_id), 
				intval($player_id),
				$new_move
			);
						
			$rest = $this->db->query($insert_move);
			if ($res === false) {
				$err = sprintf("Invalid query: %s\nWhole query: %s\n", $this->db->error, $query);
				throw new Exception($err);
			}
			
			$this->game[$new_move] = $player_id;
		}
		
		function winner() {
			$winstates = unserialize(WIN);
			$grid = $this->game;
			foreach($winstates as $boxes) {
				if ($grid[$boxes[0]] != 0) {
					if ($grid[$boxes[0]] == $grid[$boxes[1]] && $grid[$boxes[0]] == $grid[$boxes[2]]) {
						return [
							"player_id" => $grid[$boxes[0]], 
							"win_grid" => $boxes
						];
					}					
				}
			}
			if ($this->move_count == 9) {
				return [
					"player_id" => -1,
					"win_grid" => [0,0,0]
				];
			}
			
			return [
				"player_id" => 0,
				"win_grid" => [0,0,0]
			];
		}
		
		function current_player() {
			$this->load_game();
			return $this->last_player == $this->player_x ? $this->player_o : $this->player_x;
		}
		
		function player_x() {
			return $this->player_x;
		}
		
		function player_o() {
			return $this->player_o;
		}
		
		function game_state() {
			$this->load_game();
			return [
				"winner" => $this->winner(),
				"moves" => $this->game
			];
		}

	}
?>