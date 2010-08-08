<?php
if (!empty($_SERVER['SCRIPT_FILENAME']) && 'chatlogs-cd.php' == basename($_SERVER['SCRIPT_FILENAME']))
  		die ('Please do not load this page directly. Thanks!');

		
if(!empty($tables_info))
	$tables = explode(',', $tables_info);
else
	$tables = array(0 => 'chatlog');

foreach ($tables as $table) :

	$table_names[] = trim($table); // put cleaned string onto the end of the table_names array

endforeach;

$num_tables = count($table_names); // number of tables to pull data from

$limit_rows = 100;

$i = 0; // start counter at 0
$total_overall_rows = 0; // set default value to 0

## Loop thro the tables and retrieve the relevant data
while($i < $num_tables) : // write and preform query for each server
		
	// create query array
	$query = array();
	
	// write query
	$query[$i] = sprintf("SELECT id, msg_time, msg_type, msg FROM %s WHERE client_id = %s ORDER BY msg_time DESC LIMIT %s", $table_names[$i], $cid, $limit_rows);

	$db = DB_B3::getPointer();
	
	// run query
	$results = $db->mysql->query($query[$i]) or die('DB Error');
	
	while($row = $results->fetch_object()) :
	
		$records[$i][] = array(
			'id' => $row->id,
			'msg_time' => $row->msg_time,
			'msg_type' => $row->msg_type,
			'msg' => $row->msg
		);
	
	endwhile;
	
	// find num of rows found
	$num_rows_{$i} = $results->num_rows;
	
	// start count on num of total overall rows
	$total_overall_rows = $total_overall_rows + $num_rows_{$i}; // keeps last loop number plus addition of this loops num_rows
	
	// add 1 to counter
	$i++;
	$results = NULL;
			
endwhile; // end while looping thro all tables to find any records


## Spit out content if there is any ##
if($total_overall_rows > 0) :  // if total recordset not empty

echo '<div id="chatlog">
	<h3 class="cd-h cd-slide" id="cd-chat">Chat Logs <img class="cd-open" src="images/add.png" alt="Open" /></h3>
	<div id="cd-chat-table" class="slide-panel">';
	## setup tabs
	echo '<ul class="cd-tabs">';
		
		$i = 1; // set counter for server array id
		
		while($i <= $num_tables) :
			
			if($i == 1)
				echo '<li class="chat-active">';
			else
				echo '<li>';
			
			if($config['games'][$game]['servers'][$i]['name'] == NULL)
				$server_name = 'Server: '.$i;
			else
				$server_name = $config['games'][$game]['servers'][$i]['name'];
			
			echo '<a rel="chat-tab-'. $i .'"  title="View the chat logs from '. $server_name .'" class="chat-tab">'. $server_name .'</a></li>';
			
			$i++; // increment counter
							
		endwhile;
		
	echo '</ul>'; // close out tabs
	
	## RECORDS
	
	echo '<div id="chats-box">';
	
	$i_srv = 0; // set counter for server array id
	$i_tab = 1; // reset counter for second loop
	$i = 0;

	while ($i_srv < $num_tables) : // loop for 1 tab per server ?>
	
		<div id="chat-tab-<?php echo $i_tab; ?>" class="chat-content">
			<?php if($num_rows_{$i_srv} == 0) { ?>
				<p><strong>This user has no recorded chat logs for this server.</strong></p>
					<table style="display: none;">
			<?php } else { ?>
					<table>
			<?php } ?>
			
			<thead>
				<tr>
					<th></th>
					<th>Scope</th>
					<th>Message</th>
					<th>Time</th>
				</tr>
			</thead>
			<tfoot>
				<tr><td colspan="4"></td></tr>
			</tfoot>
	
		<?php // nested while loop for content
			
			if($num_rows_{$i_srv} > 0) :
				
				foreach($records[$i] as $record) : //there are still rows in results
					
					$id = $record['id'];
					$time = date($tformat, $record['msg_time']);
					$type = tableClean($record['msg_type']);
					$msg = tableClean($record['msg']);
					
					## Highlight Commands ##
					if (substr($msg, 0,1) == '!' or substr($msg, 0,1) == '@')
						$msg = '<span class="chat-cmd">'. $msg ."</span>"; 
					
					## Row color
					$alter = alter();
					
					## preapre heredoc
					$data = <<<EOD
					<tr class="$alter">
						<td>$id</td>
						<td>$type</td>
						<td>$msg</td>
						<td><em>$time</em></td>
					</tr>
EOD;
					
					echo $data; // echo content
					
				endforeach;
				
			endif;
			
		?>
			
		</table>
		</div>
	
	<?php

		$i_srv++;
		$i_tab++;
		$i++;

	endwhile; // end loop - make content for each server
	
	echo '</div>'; // close #chats-box

	echo '</div>';
	
echo '</div>'; // close #chat-logs 

endif; // end if no records return in total
?>