$(document).ready(function(){
	ini();
	$("#action").change(function(){
		var act = $(this).children("option:selected").val();
		
	switch(act){
		case "1":
			ini();
			setfield("strtable","table","");
		break;
		case "2":
			ini();
			$("#msg").text("This should be done just before transfer to drdata because it could cause miss behaviors to actions.");
			setfield("strtable","table","");
			setfield("string","string"," : Enter a new name for the table");
		break;
		case "3":
			ini();
			setfield("strtable","table","");
		break;
		case "4":
			ini();
			setfield("strtable","table","");
		break;
		case "5":
			ini();
			setfield("strtable","table"," : No need to give a new name. It will be named with the prefix 'copy'. You will be able to rename it later");
		break;
		case "6":
			ini();
			setfield("strtable","table","");
			setfield("string","newfield"," : Enter a name for the new field");
		break;
		case "7":
			ini();
			setfield("strtable","table","");
			setfield("strfield","field","");
			setfield("string","newname"," : Enter a new name for the field");
		break;
		case "8":
			ini();
			setfield("strtable","table","");
			setfield("strfield","column","");
		break;
		case "9":
			ini();
			$("#msg").text("To duplicate a column you need to identify the column that you want to duplicate, named the new column.");
			setfield("strtable","table","");
			setfield("strfield","column","");
			setfield("string","newcolumn"," : The chosen strfield will be duplicate with all his data. Enter a name for the new column.");
		break;
		case "10":
			ini();
			$("#msg").text("To split a column you need to identify the column that you want to work with, named the new column. You will need to set how much left and right caracters you want to keep. This will let you have the power to union the right and the left. If needle is not empty, right and left will be ignore.");
			setfield("strtable","table","");
			setfield("strfield","column","");
			setfield("left","left"," : a number representing the length you want to keep from the left",false);
			setfield("right","right"," : a number representing the length you want to keep from the right",false);
			setfield("string","newcolumn"," :  enter a name for the new column");
		break;
		case "11":
			ini();
			$("#msg").text("To move a column you need to identify the column that you want to move, and the table where you want to move it. Noticed that if your column contains more records than the table receiving it. It will be truncate.");
			setfield("strtable","table","");
			setfield("strfield","column","");
			setfield("totable","totable","");
		break;
		case "12":
			ini();
			setfield("strtable","table","");
			setfield("strfield","column"," : Where column *operator value. Operator could be anything in the list");
			setfield("operator","operator","");
			setfield("value","value"," : The value that will be use by the operator for comparison");
			setfield("unique","unique"," : A field name that contains only unique value. Usually begin with id_ ");
		break;
		case "13":
			ini();
			setfield("strtable","table","");
		break;
		case "14":
			ini();
			setfield("string","string"," : Enter a name for the new table.");
		break;
		case "15":
			ini();
			setfield("strtable","table","");
			setfield("strfield","column","");
			setfield("value","start"," : beginning value");
		break;
		case "16":
			ini();
			$("#msg").text("Reassign key values of a column in a slave table against new values in the master table. First you will need to duplicate a column in the master table and renumber it.");
			setfield("strtable","Master","");
			setfield("strfield","Master old key"," : This column contains original keys");
			setfield("totable","Slave"," : Slave table that will have multiple key value from master table");
			setfield("tofield","Slave key"," : Column to match the master old key and then change it for new key");
			setfield("unique","Master new key"," : Do you forget to duplicate a column ? This duplicate column contains new values.");
		break;
		case "17":
			ini();
			$("#msg").text("To move a column you need to identify the column that you want to move, and the table where you want to move it. You also need to match the keys.");
			setfield("strtable","table"," : The table that has the column you want to move");
			setfield("strfield","column"," : This column to be move");
			setfield("totable","totable"," : The table that will receive the column");
			setfield("tofield","tofield"," : Match keys of the table that will receive the column");
			setfield("unique","unique"," :  Unique key of the table that has the column you want to move");
		break;
		case "18":
			ini();
			$("#msg").text("First duplicate a column. Allow me to manually tell it what the key for the column is? example: phonetype1=\"H\" means that phone1 should move to the HomePhone column");
			setfield("strtable","table"," : The table that has the column you want to copy");
			setfield("strfield","column"," : This column to be copy to another");
			setfield("totable","totable"," : The table that will receive the column. It can be the same table.");
			setfield("tofield","tofield"," : The column that will receive the copy. It should already be created.");
			setfield("string","where"," : The field that will serve for matching condition");
			setfield("operator","operator","");
			setfield("value","value"," :  The value that will serve for matching condition",false);
		break;
		case "19":
			//Concat columns
			ini();
			$("#msg").text("First add a column to receive the concatanation. Concat two or more columns of the table.");
			setfield("strtable","table"," : The table that has columns you want to concat");
			setfield("strfield","column"," : This column to receive the concatening");
			setfield("string","filter"," : separate wanted fields with a comma ex: Addr1,City,State");
			setfield("value","value"," :  Set a result delimiter, if empty it will be a space by default",false);
		break;
		case "20":
			//Date corrector
			ini();
			$("#msg").text("To fix a column choose a column and identify the format. It will transform as YYYY-MM-DD");
			setfield("strtable","table"," : The table that has date column you want to fix");
			setfield("strfield","column"," : This date column to be corrected");
			setfield("operator","format"," : Identify the current format of the date you want to change.");
		break;
		case "21":
			//Find and Replace
			ini();
			$("#msg").text("Find and replace a text in a column");
			setfield("strtable","table"," : The table that has date column you want to search");
			setfield("strfield","column"," : Search this column");
			setfield("string","filter"," : Text to search");
			setfield("value","text"," : Replace by this text");
		break;
		case "22":
			// Merge rows
			ini();
			$("#msg").text("Merge rows to a column in the first row by matching keys.");
			setfield("strtable","table"," : The table that has rows you want to merge");
			setfield("strfield","multiple"," : Multiple keys matching rows");
			setfield("string","line"," : The field that will serve for sorting");
			setfield("unique","concatenation"," : The column that will receive the concat text. First row of all. Other rows will be deleted.");
		break;
		case "23":
			ini();
			$("#msg").text("To split a column you need to identify the column that you want to work with, named the new column. You will need to set a research string.");
			setfield("strtable","table","");
			setfield("strfield","column","");
			setfield("string","newcolumn"," :  enter a name for the new column");
			setfield("value","needle"," : needle, the research string.",false);
		break;
		case "24":
			ini();
			$("#msg").text("Copy text \"Self\" into this field when PatientNumber=Family; copy text \"Other\" into this field when PatientNumber<>Family");
			setfield("strtable","table"," : The table that has the column which will receive the copy text");
			setfield("strfield","column"," : This column to receive the copy text");
			setfield("left","text"," : This text will be copied");
			setfield("string","where"," : The field that will serve for matching condition");
			setfield("operator","operator","");
			setfield("value","value"," :  The value that will serve for matching condition",false);
		break;
		default:
			ini();
		}
	});
	
	function setfield(field,label="",help="",required="")
	{
		$("#div"+field).show();
		$("#"+field).css( "display", "block" );		
		$("#"+field).attr("required",required);
		$("#lbl"+field).text(label);
		$("#help"+field).text(help);
	}

	function ini()
	{
		$("#strtable").css( "display", "none" );
		$("#strfield").css( "display", "none" );
		$("#totable").css( "display", "none" );
		$("#tofield").css( "display", "none" );
		$("#left").css( "display", "none" );
		$("#right").css( "display", "none" );
		$("#string").css( "display", "none" );
		$("#operator").css( "display", "none" );
		$("#value").css( "display", "none" );
		$("#unique").css( "display", "none" );
		
		$("#helpstrtable").text("");
		$("#helpstrfield").text("");
		$("#helptotable").text("");
		$("#helptofield").text("");
		$("#helpleft").text("");
		$("#helpright").text("");
		$("#helpstring").text("");
		$("#helpoperator").text("");
		$("#helpvalue").text("");
		$("#helpunique").text("");
		
		$("#lblstrtable").text("");
		$("#lblstrfield").text("");
		$("#lbltotable").text("");
		$("#lbltofield").text("");
		$("#lblleft").text("");
		$("#lblright").text("");
		$("#lblstring").text("");
		$("#lbloperator").text("");
		$("#lblvalue").text("");
		$("#lblunique").text("");
		
		$("#strtable").removeAttr("required");
		$("#strfield").removeAttr("required");
		$("#totable").removeAttr("required");
		$("#tofield").removeAttr("required");
		$("#left").removeAttr("required");
		$("#right").removeAttr("required");
		$("#string").removeAttr("required");
		$("#operator").removeAttr("required");
		$("#value").removeAttr("required");
		$("#unique").removeAttr("required");
		
		$("#divstrtable").hide();
		$("#divstrfield").hide();
		$("#divtotable").hide();
		$("#divtofield").hide();
		$("#divleft").hide();
		$("#divright").hide();
		$("#divstring").hide();
		$("#divoperator").hide();
		$("#divvalue").hide();
		$("#divunique").hide();
		
		$("#msg").text("Message is not set for this action.");
	}
});