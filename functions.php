<?php


function head($pageName, $extra = null){
	
	?>
	
	<head>
		<title>Task Managment System</title>
		<!-- Stylesheet Stuff -->
		<link href="/taskManagmentSystem/main.css" rel="stylesheet" />
		<?php echo $extra; ?>
		<!-- Fonts -->
		<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
		<!-- Meta stuff -->
		<meta name="description" content="Task Managment System">
  		<meta name="keywords" content="Task Management, personal, business,">
  		<meta name="author" content="Josiah Rowden">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<script>
			// from https://stackoverflow.com/questions/133925/javascript-post-request-like-a-form-submit?page=1&tab=scoredesc#tab-top
			// but modified to use maps instead of objects for params paramiter
			function post(path, params, method='post') { 
				const form = document.createElement('form');
				form.method = method;
				form.action = path;

				params.forEach(function(value, key){
					const hiddenField = document.createElement('input');
					hiddenField.type = 'hidden';
					hiddenField.name = key;
					hiddenField.value = value;

					form.appendChild(hiddenField);
				})

				document.body.appendChild(form);
				form.submit();
			}

			/* ----------------------------- Responsive edit and add code -----------------------------  */
			
			// var editedEntries = []; old

			// NOTE: addrow functionality is piggybacking off the edit functionality so variable names
			// should be more like addOrEditrowID but that would get confusing so just sticking withe exixting names
			var editedRowID; // global var
			var editedColumns = []; // global var
			var typeEditing ; // global var
			//old 
			// function addToEditObject(IDVal, columnVal){
			// 	var editedEntry = {
			// 		editedID: IDVal,
			// 		editedColumn: columnVal,
			// 		editedValue: ""
			// 	}; // editedValue to be added later to allow user to change the value 
			// 	editedEntries.push(editedEntry); 
			// }
			function addToEditedColumns(columnNameToAdd){
				if(!editedColumns.includes(columnNameToAdd)){ // prevents duplicates 
					editedColumns.push(columnNameToAdd);
				}
			}

			
			function didplaySaveButton(){
				
				var normalFooterButtons = document.getElementById("normalFooterButtons");
				var editingFooterButtons = document.getElementById("editingFooterButtons");
				normalFooterButtons.classList.replace("showing","hidden");
				editingFooterButtons.classList.replace("hidden","showing");
			}
			function hideSaveButton(){
				var normalFooterButtons = document.getElementById("normalFooterButtons");
				var editingFooterButtons = document.getElementById("editingFooterButtons");
				editingFooterButtons.classList.replace("showing","hidden");
				normalFooterButtons.classList.replace("hidden","showing");
			}
			function displayAddButton(extra = ""){
				var addButton = document.getElementById("addNewRowButton" + extra);
				addButton.classList.remove("hidden");
			}
			
			function hideAddButton(extra = ""){
				////console.log("addNewRowButton" + extra);//test
				var addButton = document.getElementById("addNewRowButton" + extra);
				////console.log(addButton);//test
				addButton.classList.add("hidden");
				////console.log(addButton);//test
			}
			function allowEdit(columnName, IDToChange, extra = ""){
				//console.log("I RAN")// test
				
				cancel(extra) // hiding all input elements - dosent matter that it hides the buuttons as this function shows them again 
				// getting the elements to edit
				editTag = "ID" + IDToChange + extra;
				console.log("editInputs" + editTag);//test
				var allEditInputTags = document.getElementsByClassName("editInputs" + editTag);
				var allEditButtonTags = document.getElementsByClassName("editButtons" + editTag);
				console.log(allEditInputTags); //test
				console.log(allEditButtonTags); //test
				// showing only the ones clicked 
				for (let i = 0; i < allEditInputTags.length; i++) {
					allEditButtonTags[i].classList.add("hidden");
					allEditInputTags[i].classList.remove("hidden");
				}
				editedRowID = IDToChange; // so saveAndSubit() knows what row to update in the DB
				typeEditing = extra;
				addToEditedColumns(columnName)
				//console.log(columnName);
				//old

				// //console.log(columnName+"Button"+IDToChange) // test 
				// var buttonElement = document.getElementById(columnName+"Button"+IDToChange);
				// var inputElement = document.getElementById(columnName+"Input"+IDToChange);
				// // chnaging them
				// buttonElement.classList.add("hidden");
				// inputElement.classList.remove("hidden"); 
				// // adding to dictionary for when data is sentto the data base
				// addToEditObject(IDToChange, columnName);

				didplaySaveButton(extra); 
			}

			function addNewRow(extra = ""){
				// getting the elements
				var newRow = document.getElementById("newRow" + extra);
				// chnaging them
				hideAddButton(extra);
				cancel(extra) // hiding all input elements
				newRow.classList.remove("hidden");
				didplaySaveButton(extra);
				////console.log(extra)//test
				//seting up varibles 
				editedRowID = "NEW"; // used to distinguish from editing
				//adding all the columns
				////console.log("addRowNEW" + extra);//test
				var addRowColumns = document.getElementsByClassName("addRowNEW" + extra);
				//console.log(addRowColumns);//test
				for (column of addRowColumns) {
					//var i = 0;// test
					////console.log(column.name) //test 
					column = column.name.replace("InputNEW" + extra,""); 
					////console.log(column) //test 
					addToEditedColumns(column);
					////console.log("I RAN " + i++)//test 
				}
				
				
			}

			// function noNewRow(extra = ""){
			// 	// getting the elements
			// 	////console.log("newRow" + extra)//test
			// 	var newRow = document.getElementById("newRow" + extra);
			// 	// chnaging them
			// 	newRow.classList.add("hidden");
			// 	displayAddButton(extra);
			// 	hideSaveButton(extra);
			// }

			function saveAndSubmit(extra = ""){
				// NOTE: addrow functionality it piggybacking off the edit functionality so variable names
				// should be more like addOrEditrowID but that would get confusing so just sticking withe exixting names
				var editedRowValues = new Map();
				editedRowValues.set("ID", editedRowID);
				console.log("Edited row Id: " + editedRowID) ;//test
				//var editedRow = document.getElementsByClassName("editInputsID" + editedRowID);
				for (let i = 0; i < editedColumns.length; i++) {
					var tagToGrab = editedColumns[i] + "Input" + editedRowID + extra;
					//console.log(tagToGrab);//test
					var editedValElement = document.getElementById(tagToGrab);
					console.log(editedValElement)//test 
					if (editedValElement.tagName == "select"){
						editedVal = editedValElement.options[editedValElement.selectedIndex].value;
					}else{
						editedVal = editedValElement.value;
					}
					editedRowValues.set(editedColumns[i], editedVal); 
				}
				editedRowValues.set("tokenETL", tokenETL); 
				post("<?php echo $pageName; ?>", editedRowValues); 
			}
			
			function cancel(extra = ""){
				// hiding all input elements
				var allInputTags = document.getElementsByClassName("editInputs");
				var allButtonTags = document.getElementsByClassName("editButtons");
				for (let i = 0; i < allInputTags.length; i++) {
					allInputTags[i].classList.add("hidden");
					allButtonTags[i].classList.remove("hidden");
				}
				//noNewRow(extra) 
				hideSaveButton();
			}

			function deleteRow(IDtoDelete, pageFrom){
				if (confirm("Do You Want To Delete This Row? ")) {
					var IDtoDeleteMap = new Map();
					IDtoDeleteMap.set("Xdata", IDtoDelete);
					IDtoDeleteMap.set("pageFrom", pageFrom);  
					post("delete.php", IDtoDeleteMap);
				}
			}

			function errorMsg(errors){
				if(typeof(errors) !== 'undefined' ){
					//console.log(errors);// test 
					for(const [key, value] of Object.entries(errors)){ //loop through key pair
						var i = 0;
						//console.log(i++);//test
						//console.log(key);//test
						var inputTag =  document.getElementById(key);
						var errorMsgTag = document.getElementById(key + "Error");
						//inputTag.innerHTML = "test"; 
						console.log(inputTag);//test
						console.log(errorMsgTag);//test
						inputTag.classList.add("errorInput");
						errorMsgTag.innerHTML = "<p id = 'msg'><b class = 'error'>" + value + "</b></p>";
						//console.log(errorMsgTag);//test
					}
				}
			}

			function changeTab(tagToChangeTo){
				// get selected tab, remove selected class
				console.log("tagToChangeTo: "+ tagToChangeTo);
				var selectedTab = document.getElementsByClassName("selected");
				//console.log(selectedTab);//test
				selectedTab[0].classList.remove("selected"); // [0] as getElementsByClassName returns a list of 1
				// get tab clicked on, add selected class
				var tabClicked = document.getElementById(tagToChangeTo + "Tab");
				//console.log(tabClicked); //test 
				tabClicked.classList.add("selected");
				// change to relevent display
					// get container without hidden add hidden class
				var containerShowing = document.getElementsByClassName("showing");
				containerShowing[0].classList.add("hidden"); // [0] as getElementsByClassName returns a list of 1
				containerShowing[0].classList.remove("showing"); // [0] as getElementsByClassName returns a list of 1
				
					// change contain to show
				var containerToShow = document.getElementById(tagToChangeTo + "Container");
				//console.log(containerToShow); //test
				containerToShow.classList.add("showing");
				containerToShow.classList.remove("hidden");
			}

			function shiftTab(newTabID, tabsOpen, maxTabsOpen=3){
				// Shifts tabs over so they work for css 
				console.log(newTabID + " shift tab Ran");//test
				var newTab = document.getElementById(newTabID + "Tab");
				//console.log(newTab);//test
				var leftShift = -(tabsOpen * 20);
				newTab.style.left  = leftShift + "px";
				newTab.style.zIndex = (maxTabsOpen - tabsOpen);
				newTab.style.order = tabsOpen;
				//console.log(newTab);//test
				// return tabsOpen + 1;
			}
		</script>
	
	<?php
	// destroyUnwantedSession($pageName);

	//var_dump($_SESSION['previous']);//test 

	// for destroyUnwantedSession 
	// if(in_array($pageName, $_SESSION['previous']) != true){ // avoids repeats
	// 	array_push($_SESSION['previous'],$pageName); //adds the current page to the top of the stack
	// }else{

	// }
	// no head close as it is done on individual page so custom scripts can be included into the head 
}
function navBar($displaybuttons,$currentPageName = null){
	//echo $currentPageName;//test
	?>
	<header id="header">
        <nav>
            <img class="logoImage" src = "./imagesTMS/logoNoBG.png" alt = "Logo"/>
		    
            <?php 
			if (isset($_SESSION["loggedIn"])){
				echo"
				<h1>Welcome ".$_SESSION['username']."</h1>
				<a href='LOProcess.php' class = 'button'>Log Out</a>
            	<button>Cog</button>
				";
			}else{

				echo"<h1>Task Managment System</h1>
				<div></div>" 						// prints empty div to centre title
				; 
			}
			?>
            
        </nav>
	</header>
	
	<form></form> <!-- to fix Chrome being Weird -->
	<?php

}

function notLoggedIn(){
	//checks if not logged in 
	if (!isset($_SESSION["loggedIn"]) and ($_SESSION["loggedIn"] != true)) {
		header("location: logout.php"); // if so redirects the user  to the logout page as to unset any sessions they may have set already. 
	}									// this will then send the user to the login page 
	
}

// function destroyUnwantedSession($pageName){
// 	//destroys unwanted error messages from other pages 
// 	if (isset($_SESSION['previous'])) {
// 		if ($pageName != end($_SESSION['previous'])) { // checks if this is the page the error was meant to display on 
// 			unset($_SESSION['msg']); // unset's the msg session if so 
// 		} else {

// 		}
// 	} else {

// 	}
// }

function errorMsg($msg){
	echo $msg;
}

function yesOrNo($truthVal){
	// turns a true false or 1, 0 value into a yes or no value
	switch($truthVal){
		case 1:
		case "1":
		case true:
		case "true":
			return("Yes");
			// no Break as it returns
		case 0:
		case "0":
		case false:
		case "false":
			return("No");
		case null:
			return("None");
		default:
			return $truthVal; // ie not a truth value
	}
}

function whitchDBColumn($ColumnName){
	switch($ColumnName){
		case "taskListName":
			$column = "name";
			break;
		default:
			$column = $ColumnName ;
	}
	return($column); 
}

function getNameFromID($ID,$con){
	$qry = "SELECT `name` FROM user WHERE ID = ?"; 
	$stmt = $con->prepare($qry);
    $stmt->execute($ID);
	return implode($stmt->fetch(PDO::FETCH_ASSOC));
}

function footer()
{
	?>
	<footer id="footer" class="bottom">
		<?php 
			if (isset($_SESSION["loggedIn"])){
				echo"
				<div id = 'normalFooterButtons' class = 'showing'>
					<button onclick='newTaskList()'class='button green'>New Tasklist</button>
					<button onclick='newTask()' class='button green'>New Task</button>
				</div>

				<div id = 'editingFooterButtons' class = 'hidden'>
					<h2>Editting:</h2>
					<button id = 'saveButton' class = 'button green ' onclick = 'saveAndSubmit(typeEditing)'>Save</button>
					<button id = 'cancelButton' class = 'button red ' onclick = 'cancel()'>Cancel</button>
				</div>
				";
			}else{

			}
		?>
		<p>Background image created using AI image generator from craiyon.com</p>
	</footer>
	<?php
}

function generateCollabCode($con){
	$newCode = rand(10000000, 99999999);
	$qry = "SELECT collabCode FROM user	WHERE collabCode = ?";
    $stmt = $con->prepare($qry);
    $stmt->execute([$newCode]);
	$count = $stmt->rowCount();
	if($count == 0){
		return $newCode;
	}else{
		return generateCollabCode($con);
	}
	
}


?>