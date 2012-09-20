// ----------------------------------------------------------
// -- PmxBlogSettings.javascript.inc                       --
// ----------------------------------------------------------
// -- Version: 1.1 for SMF 2.0                             --
// -- Copyright 2006..2008 by: "Feline"                    --
// -- Copyright 2009-2012 by: PortaMx corp.                --
// -- Support and Updates at: http://portamx.com           --
// ----------------------------------------------------------

function funcByName(fName)
{
	if(!isEditMode||(isEditMode && fName.substr(0,9)=="UpdateCat"))
	{
		var fName = fName.replace(/\d+/, "("+ fName.match(/\d+/) +")");
		eval(fName);
	}
}
function setChanged(id,what)
{
	if(cats[id]["chgtype"] !="insert")
		cats[id]["chgtype"]=what;
		haveChanged=true;
		document.getElementById("SaveButton").style.display="";
}
function removeNode(id)
{
	var node=document.getElementById("categorie_edit");
	for(var i=0;i<node.childNodes.length;i++)
	{
		var cnode=node.childNodes[i];
		if(cnode.nodeType==1)
		{
			if(cnode.getAttribute("id")=="entry"+id)
				var entry=cnode;
		}
	}
	del=document.getElementById("categorie_edit").removeChild(entry);
}
function DeleteCat(id)
{
	Doit=confirm(confDelText+"\""+cats[id]["name"]+"\"?");
	if(Doit)
	{
		removeNode(id);
		setChanged(id,"delete");
		if(id==numcats)
			numcats--;
		showMover();
	}
}
function EditCatName(id)
{
	ToggleEditmode(id);
	document.getElementById("catname"+id).focus();
}
function ToggleEditmode(id)
{
	mode=document.getElementById("edit"+id).style.display;
	isEditMode=(mode=="none"?true:false);
	document.getElementById("edit"+id).style.display=(mode=="none"?"":"none");
	document.getElementById("show"+id).style.display=(mode=="none"?"none":"");
	document.getElementById("AddCat").style.display=(mode=="none"?"none":"");
	document.getElementById("SaveButton").style.display=haveChanged?(mode=="none"?"none":""):"none";
}
function changeAttr(node,nID)
{
	for(var i=0;i<node.childNodes.length;i++)
	{
		var cnode=node.childNodes[i];
		if(cnode.nodeType==1 && cnode.getAttribute("id"))
			cnode.setAttribute("id",cnode.getAttribute("id").replace(/\d+/,nID));
			if(cnode.childNodes.length>0)
				changeAttr(cnode,nID);
	}
}
function NewCategorie()
{
	document.getElementById("SaveButton").style.display="none";
	var newcat=document.getElementById("clone").cloneNode(true);
	var nID=savecats+1;
	newcat.setAttribute("id","entry"+nID);
	newcat.removeAttribute("style");
	changeAttr(newcat,nID);
	document.getElementById("categorie_edit").appendChild(newcat);
	document.getElementById("catname"+nID).value="";
	document.getElementById("edit"+nID).firstChild.data="";
	ToggleEditmode(nID);document.getElementById("catname"+nID).focus();
}
function UpdateCat(id)
{
	if(!document.getElementById("txt"+id).hasChildNodes())
	{
		if(document.getElementById("catname"+id).value.replace(/\s/g,"") !="")
		{
			ncat=document.getElementById("catname"+id).value;
			var chld=document.createTextNode(ncat);
			document.getElementById("txt"+id).appendChild(chld);
			cats[id]=new Object();
			cats[id]["id"]=0;
			cats[id]["name"]=document.getElementById("catname"+id).value;
			if(numcats>=0)
			{
				lorder=cats[numcats]["corder"];
				cats[id]["depth"]=cats[numcats]["depth"];
			}
			else
			{
				lorder=numcats;
				cats[id]["depth"]=0;
			}
			cats[id]["corder"]=++lorder;
			cats[id]["chgtype"]="";
			if(cats[id]["depth"]>0)
			{
				dstr="";
				for(var i=0;i<cats[id]["depth"];i++)
					dstr+="..";
				var cld=document.createTextNode(dstr);
				document.getElementById("dpt"+id).appendChild(cld);
			}
			setChanged(id,"insert");
			numcats++;
			savecats++;
			ToggleEditmode(id);
		}
		else
		{
			ToggleEditmode(id);
			removeNode(id);
			if(haveChanged)
				document.getElementById("SaveButton").style.display="";
		}
	}
	else
	{
		if(document.getElementById("catname"+id).value !=document.getElementById("txt"+id).firstChild.data)
		{
			document.getElementById("txt"+id).firstChild.data=document.getElementById("catname"+id).value;
			cats[id]["name"]=document.getElementById("catname"+id).value;setChanged(id,"update");
		}
		ToggleEditmode(id);
	}
	showMover();
}
function checkMoveL(id,p,n)
{
	var dPC=(id+p>=0?cats[id+p]["depth"] - cats[id]["depth"]:0);
	var dNC=(id+n<=numcats?cats[id]["depth"] - cats[id+n]["depth"]:0);
	return(id>0 && numcats>0 && cats[id]["depth"]>0 &&(id<numcats &&((dPC==0 && dNC==1)||(dPC==-1 &&(dNC==1||dNC==0||cats[id+n]["depth"]==0))))||(id==numcats && cats[id]["depth"]>0 &&(dPC==-1||(dPC==0 && cats[id]["depth"]==1))))?true:false;
}
function checkMoveR(id,p,n)
{
	var dPC=(id+p>=0?cats[id+p]["depth"] - cats[id]["depth"]:0);
	var dNC=(id+n<=numcats?cats[id]["depth"] - cats[id+n]["depth"]:0);
	return(id>0 && numcats>0 &&(id<numcats &&(dPC==0 &&((dNC==0 && cats[id+n]["depth"]==0)||(dNC==1||dNC==-1||cats[id+n]["depth"]==0))||(dPC==1 && dNC==0)))||(id==numcats && numcats>0 &&(dPC==0||dPC==1)))?true:false;
}
function checkMoveU(id,p,n)
{
	return(numcats>0 &&(id>0&&(cats[id+p]["depth"]==cats[id]["depth"]||(cats[id+p]["depth"] - cats[id]["depth"]==1 && id>1))))?true:false;
}
function checkMoveD(id,p,n)
{
	return(numcats>0 && id<numcats&&(cats[id+n]["depth"]==cats[id]["depth"]||(cats[id]["depth"]==1 && cats[id+n]["depth"]==0)))?true:false;
}
function getOfset(id)
{
	var prv=-1;
	var nxt=1;
	while(id+prv>=0 && cats[id+prv]["chgtype"]=="delete")
		prv--;
	while(id+nxt<=numcats && cats[id+nxt]["chgtype"]=="delete")
		nxt++;
	return Array(prv,nxt);
}
function showMover()
{
	for(var i=0;i<=numcats;i++)
	{
		if(cats[i]["chgtype"] !="delete")
		{
			ofs=getOfset(i);
			document.getElementById("MoveCatL"+i).style.display=checkMoveL(i,ofs[0],ofs[1])?"":"none";
			document.getElementById("MoveCatR"+i).style.display=checkMoveR(i,ofs[0],ofs[1])?"":"none";
			document.getElementById("MoveCatU"+i).style.display=checkMoveU(i,ofs[0],ofs[1])?"":"none";
			document.getElementById("MoveCatD"+i).style.display=checkMoveD(i,ofs[0],ofs[1])?"":"none";
		}
	}
}
function execMoveLR(id)
{
	dstr="";
	for(var i=0;i<cats[id]["depth"];i++)
		dstr+="..";
	if(document.getElementById("dpt"+id).hasChildNodes())
	{
		var cld=document.getElementById("dpt"+id).firstChild;
		document.getElementById("dpt"+id).removeChild(cld);
	}
	if(dstr !="")
	{
		var cld=document.createTextNode(dstr);
		document.getElementById("dpt"+id).appendChild(cld);
	}
	setChanged(id,"update");
	showMover();
}
function execMoveUD(sid,did)
{
	t=cats[sid];
	tsord=cats[sid]["corder"];
	tdord=cats[did]["corder"];
	cats[sid]=cats[did];
	cats[did]=t;
	cats[sid]["corder"]=tsord;
	cats[did]["corder"]=tdord;
	document.getElementById("txt"+sid).firstChild.data=cats[sid]["name"];
	document.getElementById("txt"+did).firstChild.data=cats[did]["name"];
	document.getElementById("catname"+sid).value=cats[sid]["name"];
	document.getElementById("catname"+did).value=cats[did]["name"];
	execMoveLR(sid);execMoveLR(did);
}
function MoveCatL(id)
{
	ofs=getOfset(id);
	if(checkMoveL(id,ofs[0],ofs[1]))
	{
		cats[id]["depth"]--;
		execMoveLR(id);
	}
}
function MoveCatR(id)
{
	ofs=getOfset(id);
	if(checkMoveR(id,ofs[0],ofs[1]))
	{
		cats[id]["depth"]++;
		execMoveLR(id);
	}
}
function MoveCatU(id)
{
	ofs=getOfset(id);
	if(checkMoveU(id,ofs[0],ofs[1]))
		execMoveUD(id,id+ofs[0]);
}
function MoveCatD(id)
{
	ofs=getOfset(id);
	if(checkMoveD(id,ofs[0],ofs[1]))
		execMoveUD(id,id+ofs[1]);
}
function submitEditCats()
{
	for(var i=0;i<=savecats;i++)
	{
		if(cats[i]["chgtype"] !="")
		{
			AddCatOption("id", cats[i]["id"]);
			AddCatOption("name", cats[i]["name"]);
			AddCatOption("corder", cats[i]["corder"]);
			AddCatOption("depth", cats[i]["depth"]);
			AddCatOption("chgtype", cats[i]["chgtype"]);
		}
	}
	document.PmxBlog_CatED.submit();
}
function AddCatOption(elmid, value)
{
	var oLen = document.getElementById("pmxblog_cat_"+elmid).options.length;
	var oElm = new Option(document.getElementById("pmxblog_cat_"+elmid), value, false, true);
	document.getElementById("pmxblog_cat_"+elmid).options[oLen] = oElm;
	document.getElementById("pmxblog_cat_"+elmid).options[oLen].selected = true;
}
