
<?php
	require 'gstable.php';
	require 'tictactoe.php';
	
	if (!empty($_POST)) {
		try {
			$gstable = new GSTable;
			$gstable->parseJSON($_POST["params"]);

			$game = new TicTacToe($_GET["game_id"]);
			$moves = $gstable->getColumn(0);
			$game->updateGame($moves);

			error_log ($gstable->toJSON());
			echo "{\"Status\":\"Success\"}";
		} catch(Exception $e) {
			error_log("ACK");
			echo "{\"Status\":\"Fail\"}";
		}
	} else {
		
		if ($_GET["table"] == "GetPlayer") {
			# Make new row in Player table.
			require 'player.php';
			$player_id = getPlayerID($_GET["username"], "blah");
			error_log("==============================" + $player_id);
			$gstable = new GSTable(
				[
					["", GSTable::TYPE_INTEGER],
				]
			);
			$gstable->table_name = "id149349";
			$gstable->push([intval($player_id)]);
		} else {
			$game = new TicTacToe($_GET["game_id"]);
			if ($_GET["table"] == "GameList") {
				$gstable = new GSTable(
					[
						["game_id", GSTable::TYPE_STRING],
						["player_x", GSTable::TYPE_STRING],
						["player_o", GSTable::TYPE_STRING],
						["date", GSTable::TYPE_STRING]
					]
				);

				$gstable->push([
					"Game",
					"X",
					"O",
					"Date"
				]);

				foreach (TicTacToe::active_games($_GET["player_id"]) as $row) {
					date_default_timezone_set("UTC");
					$game_date = date("d M Y", strtotime($row["created_at"]));
					$gstable->table_name = "id881230";
					$gstable->push([
						$row["game_id"],
						$row["player_x"],
						$row["player_o"],
						$game_date
					]);
				}
			} elseif ($_GET["table"] == "GameInfo") {
				$gstable = new GSTable(
					[
						["", GSTable::TYPE_INTEGER]
					], 
					["game_id", "player_x", "player_o", "current_player", "winner"]
				);
				$gstable->table_name = "id480350";
				$gstable->getByRowLabel("game_id")[0] = $_GET["game_id"];
				$gstable->getByRowLabel("player_x")[0] = $game->player_x();
				$gstable->getByRowLabel("player_o")[0] = $game->player_o();
				$gstable->getByRowLabel("current_player")[0] = $game->current_player();
				if (!$game->winner) {
					$game->load_game();
				}
				$gstable->getByRowLabel("winner")[0] = $game->winner()["player_id"];

			} elseif ($_GET["table"] == "GameGrid") {
				$gstable = new GSTable(
					[
						["player", GSTable::TYPE_INTEGER],
						["win",    GSTable::TYPE_INTEGER]
					],
					["","","","","","","","",""]
				);
				$gstable->table_name = "id703100";
				$game_state = $game->game_state();
				for ($i = 0; $i < 9; $i++) {
					$gstable[$i] = [intval($game_state["moves"][$i])];				
				}

				if ($game_state["winner"]["player_id"] > 0) {
					for ($i = 0; $i < 3; $i++) {

						$win_idx = intval($game_state["winner"]["win_grid"][$i]);
						$win_spot = $gstable[$win_idx];
						$gstable[$win_idx] = [$win_spot[0], 1];
					}				
				}
			}
		}

		error_log($gstable->toJSON());		
		echo $gstable->toJSON();
	}
?>