function openSmallWin(url, name)
{
    wid=window.open("index.php?r="+url+"&noframe=1", name,'toolbar=no,scrollbars=yes,resizable=yes,top=10,left=500,width=800,height=900'); 
    window.wid.focus();
}

function openWin(url, name)
{
  wid=window.open("index.php?r="+url+"&noframe=1", name,'toolbar=no,scrollbars=yes,resizable=yes,top=10,left=500,width=1350,height=900'); 
  window.wid.focus();
}
function openFullWin(url, name)
{
  wid=window.open("index.php?r="+url+"&noframe=1", name,'fullscreen=yes,toolbar=no,scrollbars=yes,resizable=yes'); 
  window.wid.focus();
}

function openToolBarWin(url, name)
{
  wid=window.open("index.php?r="+url, name,'toolbar=yes,scrollbars=yes,resizable=yes,top=10,left=500,width=1150,height=800'); 
  window.wid.focus();
}

function openExtWin(url, name)
{
  wid=window.open(url, name,'toolbar=no,scrollbars=yes,resizable=yes,top=10,left=500,width=1150,height=700'); 
  window.wid.focus();
}

function openSwitchWin(url)
{
  wid=window.open("index.php?r="+url+"&noframe=1",'successwin','toolbar=no,scrollbars=yes,resizable=yes,top=10,left=500,width=50,height=50'); 
 //window.wid.focus();
}

function openEditWin(url)
{
  wid=window.open("index.php?r="+url,'editwin','toolbar=no,scrollbars=yes,resizable=yes,top=10,left=500,width=800,height=600'); 
  window.wid.focus();
}

/*obsoleted*/
function view(n) {
    style = document.getElementById(n).style;
    style.display = (style.display == 'block') ? 'none' : 'block';
}

function switchView(n) {
    style = document.getElementById(n).style;
    style.display = (style.display == 'block') ? 'none' : 'block';
}


function doCall(id)
{  	
  window.open("<?php echo $curUser->phoneLink; ?>"+document.forms["w0"][id].value,'_blank','toolbar=no,scrollbars=yes,resizable=yes,top=75,left=550,width=100,height=100'); 	
}

/*****************************************/
/***** Диалоги  **************************/
/*****************************************/

function showDialog(dialog_id)
{   
//Показ диалога
		$('#overlay').fadeIn(400, // сначала плавно показываем темную подложку
		 	function(){ // после выполнения предидущей анимации
				$(dialog_id) 
					.css('display', 'block') // убираем у модального окна display: none;
					.animate({opacity: 1, top: '50%'}, 200); // плавно прибавляем прозрачность одновременно со съезжанием вниз
		});
}

function closeDialog(dialog_id)
	{ // ловим клик по крестику или подложке
	
		$(dialog_id)
			.animate({opacity: 0, top: '45%'}, 200,  // плавно меняем прозрачность на 0 и одновременно двигаем окно вверх
				function(){ // после анимации
					$(this).css('display', 'none'); // делаем ему display: none;
					$('#overlay').fadeOut(400); // скрываем подложку
				}
			);
	}

