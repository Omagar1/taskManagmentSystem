<?php


function head($pageName, $extra = null){
	
	?>
	
	<head>
		<title>TaskMaster Pro</title>
		<!-- Stylesheet Stuff -->
		<link href="/taskManagmentSystem/main.css" rel="stylesheet" />
		<?php echo $extra; ?>
		<!-- Fonts -->
		<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
		<!-- Meta stuff -->
		<meta name="description" content="TaskMaster Pro">
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
				
				cancel(extra) // hiding all input elements so that only the one reccord is edited at a time
				// getting the elements to edit
				editTag = "ID" + IDToChange + extra;
				console.log("editInputs" + editTag);//test
				var allEditInputTags = document.getElementsByClassName("editInputs" + editTag);
				var allEditButtonTags = document.getElementsByClassName("editButtons" + editTag);
				//console.log(allEditInputTags); //test
				//console.log(allEditButtonTags); //test
				// showing only the ones clicked 
				for (let i = 0; i < allEditInputTags.length; i++) {
					allEditButtonTags[i].classList.add("hidden");
					allEditInputTags[i].classList.remove("hidden");
				}
				editedRowID = IDToChange; // so saveAndSubit() knows what row to update in the DB
				typeEditing = extra;
				//console.log("columnName: " + columnName); //test
				addToEditedColumns(columnName);
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
				//console.log("Edited row Id: " + editedRowID) ;//test
				//var editedRow = document.getElementsByClassName("editInputsID" + editedRowID);
				for (let i = 0; i < editedColumns.length; i++) {
					var tagToGrab = editedColumns[i] + "Input" + editedRowID + extra;
					//console.log(tagToGrab);//test
					var editedValElement = document.getElementById(tagToGrab);
					//console.log(editedValElement)//test 
					if (editedValElement.tagName == "select"){
						editedVal = editedValElement.options[editedValElement.selectedIndex].value;
					}else{
						editedVal = editedValElement.value;
					}
					editedRowValues.set(editedColumns[i], editedVal); 
				}
				if(extra == "TL" ){
					editedRowValues.set("tokenETL", tokenETL);
				}else if(extra == "TK" ){
					editedRowValues.set("tokenETK", tokenETK);
				}else if(extra == "SG"){
					editedRowValues.set("tokenESG", tokenESG);
				}else if(extra == "UR"){
					editedRowValues.set("tokenEUR", tokenEUR);
				}
				 
				post("<?php echo $pageName; ?>", editedRowValues); 
			}
			
			function cancel(extra = ""){
				// hiding all input elements
				var allInputTags = document.getElementsByClassName("editInputs");
				var allButtonTags = document.getElementsByClassName("editButtons");
				
				for (let i = 0; i < allInputTags.length; i++) {
					//console.log(allButtonTags[i]); // test
					allInputTags[i].classList.add("hidden");
					allButtonTags[i].classList.remove("hidden");
				}
				//noNewRow(extra)
				// if (typeEditing =="SG"){
				// 	document.getElementById("weighting"+editedRowID+"SG").classList.remove("ignore");//removing the ignore 
				// }
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
						//console.log(inputTag);//test
						//console.log(errorMsgTag);//test
						//console.log(typeof(inputTag))//test
						if(inputTag != null){ // not all errors will have an input tag to change display of
							inputTag.classList.add("errorInput");
						}
						errorMsgTag.innerHTML = "<p id = 'msg'><b class = 'error'>" + value + "</b></p>";
						//console.log(errorMsgTag);//test
					}
				}
			}

			function changeTab(tagToChangeTo){
				// get selected tab, remove selected class
				//console.log("tagToChangeTo: "+ tagToChangeTo); //test
				var selectedTab = document.getElementsByClassName("selected");
				//console.log(selectedTab);//test
				selectedTab[0].classList.remove("selected"); // [0] as getElementsByClassName returns a list of 1
				// get tab clicked on, add selected class
				var tabClicked = document.getElementById(tagToChangeTo + "Tab");
				//console.log(tabClicked); //test 
				//console.log("tag: "+tagToChangeTo + "Tab")
				tabClicked.classList.add("selected");
				// change to relevent display
					// get container without hidden add hidden class
				var containerShowing = document.getElementsByClassName("showing");
				containerShowing[0].classList.add("hidden"); // [0] as getElementsByClassName returns a list of 1
				containerShowing[0].classList.remove("showing"); // [0] as getElementsByClassName returns a list of 1
				
					// change contain to show
				var containerToShow = document.getElementById(tagToChangeTo + "Container");
				//console.log("containerToShow"+containerToShow); //test
				containerToShow.classList.add("showing");
				containerToShow.classList.remove("hidden");
			}

			function shiftTab(newTabID, tabsOpen, maxTabsOpen=3){
				// Shifts tabs over so they work for css 
				//console.log(newTabID + " shift tab Ran");//test
				var newTab = document.getElementById(newTabID + "Tab");
				//console.log(newTab);//test
				var leftShift = -(tabsOpen * 20);
				newTab.style.left  = leftShift + "px";
				newTab.style.zIndex = (maxTabsOpen - tabsOpen);
				newTab.style.order = tabsOpen;
				//console.log(newTab);//test
				// return tabsOpen + 1;
			}

			function getCurrentDateTime(){
				// modified from https://stackoverflow.com/questions/10211145/getting-current-date-and-time-in-javascript
				var currentdate = new Date(); 
				var datetime =  currentdate.getFullYear() + "-"
								+ (currentdate.getMonth()+1)  + "-" 
								+ currentdate.getDate() + " "  
								+ currentdate.getHours() + ":"  
								+ currentdate.getMinutes() + ":" 
								+ currentdate.getSeconds(); // plus one on month is needed
				return datetime; 
			}

			function addGeneralErrorMsg(msgToAdd, colour){
				clearGeneralErrorMsg(); // clears before adding
				var msgElement = document.getElementById("genralErrorMsg")
				msgElement.innerHTML = msgToAdd;
				msgElement.classList.add(colour); 
			}
			function clearGeneralErrorMsg(){
				var msgElement = document.getElementById("genralErrorMsg")
				msgElement.innerHTML = "";
				msgElement.classList = ""; 
			}
			function useAJAXedit(editData){
				addGeneralErrorMsg("editing...", "green");
				var editDataString = ""; 
				for(const [key, value] of Object.entries(editData)){
					editDataString = editDataString + key +"="+ value+"&";
				}
				editDataString = editDataString.slice(0, -1); // removeing last & so no errors
				
				//console.log("editDataString: " + editDataString) //
				// upadating the DB
				var xmlhttp = new XMLHttpRequest();
				xmlhttp.onreadystatechange = function() {
				if (this.readyState == 4 && this.status == 200) {
					//console.log("edit responce: "+this.responseText); //test
					clearGeneralErrorMsg();
					return this.responseText;
				}else if(this.readyState == 4 && (this.status == 403 || this.status == 404 )) {
					addGeneralErrorMsg("editing failed; oops!", "red");
				}
				};

				
				xmlhttp.open("GET", "AJAXeditRow.php?" + editDataString , true);
				xmlhttp.send()
			}


			function useAJAXdelete(IDToDelete,tableFrom, extra=null){
				if (tableFrom == "tasklistcollab" && userID != extra && userID != IDToDelete){ //extra will be ownerID if tableFrom == "tasklistcollab"
					addGeneralErrorMsg("You cannot remove other users if you are not the owner of the Tasklist", "red");
				}else if (tableFrom == "tasklist" && userID != extra) { //extra will be ownerID if tableFrom == "tasklist"
					addGeneralErrorMsg("You cannot the Tasklist if you are not the owner of the Tasklist", "red");
				}else if (confirm("are you sure?")) {
					// loading msg 
					addGeneralErrorMsg("Removing " + tableFrom + "...", "green");
					
					//from data base - using ajax
					var deleteUsing = "ID";
					if(tableFrom == "tasklistcollab"){
						deleteUsing = "userID"; 
					}
					var xmlhttp = new XMLHttpRequest();
					xmlhttp.onreadystatechange = function() {
					if (this.readyState == 4 && this.status == 200) {
						// document.getElementById("msg").innerHTML = this.responseText;
						//console.log("delete responce: "+this.responseText); //test
						//visual - only if DB change worked  
						if(tableFrom == "tasklist"){
							closeTaskList(IDToDelete);// if open 
							document.getElementById("allRow"+IDToDelete).classList.add("hidden");
						}else if(tableFrom == "task"){
							hideTask(IDToDelete);
						}else if( tableFrom == "stage"){
							hideStage(IDToDelete);
							changeWeighting(extra);
							changeWeightingsInDB(extra);	
						}else if(tableFrom == "tasklistcollab"){
							hideUser(IDToDelete); 
						}else if(tableFrom="user"){
							window.location.replace("LOProcess.php");
							console.log("AJAX Response: "+this.responseText);//test
						}

						clearGeneralErrorMsg();
					}else if(this.readyState == 4 && (this.status == 403 || this.status == 404 )) {
						addGeneralErrorMsg("Removing the " + tableFrom + " failed; oops!", "red");
					}
					};
					xmlhttp.open("GET", "AJAXdelete.php?ID=" + IDToDelete + "&table=" + tableFrom + "&whereCon=" + deleteUsing, true);
					xmlhttp.send();
				} 
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
		    
            <?php if (isset($_SESSION["loggedIn"])): ?>
				<div></div><!--  prints empty div to centre h1 tag -->
				<h1>Welcome <?php echo $_SESSION['username'];?></h1>
				<a href='LOProcess.php' class = 'button'>Log Out</a>
				<?php if($currentPageName == "settings.php"):?>
					<a href="mainPage.php" class ="button">Back</a>
				<?php else:?>
					<a href="settings.php" class ="button">Settings</a>
				<?php endif?>
            	
				
			<?php else:?>

				<h1>TaskMaster Pro</h1>
				<div></div><!--  prints empty div to centre h1 tag -->
				
			<?php endif?>
            
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
	// turns a true false or 1, 0 value into a yes or no value if there is a non boolean value it returns th value 
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
			// no Break as it returns
		case null:
			return("None");
			// no Break as it returns
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
	try{
		$qry = "SELECT `username` FROM user WHERE ID = ?"; 
		$stmt = $con->prepare($qry);
		$stmt->execute([$ID]);
		return implode($stmt->fetch(PDO::FETCH_ASSOC));
	} catch (PDOException $e) {
		//echo "Error : ".$e->getMessage();// dev error mesage
		return "error";
	}
}

function footer($pageName = ""){
	?>
	<footer id="footer" class="bottom">
		<div id="genralErrorMsg" class=""></div>
		<?php if (isset($_SESSION["loggedIn"])): ?>
				<?php if($pageName == "mainPage.php"):?>
				<div id = 'normalFooterButtons' class = 'showing'>
					<button onclick='newTaskList()'class='button green'>New Tasklist</button>
					<button onclick='newTask()' class='button green'>New Task</button>
				</div>
				<?php else:?>
				<div id="normalFooterButtons"></div><!-- empyt div so functions still work -->
				
				<?php endif;?> 
				<div id = 'editingFooterButtons' class = 'hidden'>
					<h2>Editting:</h2>
					<button id = 'saveButton' class = 'button green ' onclick = 'saveAndSubmit(typeEditing)'>Save</button>
					<button id = 'cancelButton' class = 'button red ' onclick = 'cancel()'>Cancel</button>
				</div>
				
			<?php endif;?> 
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