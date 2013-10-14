<?
 /*                        Copyright 2005 Fl�vio Ribeiro

         This file is part of OCOMON.

         OCOMON is free software; you can redistribute it and/or modify
         it under the terms of the GNU General Public License as published by
         the Free Software Foundation; either version 2 of the License, or
         (at your option) any later version.

         OCOMON is distributed in the hope that it will be useful,
         but WITHOUT ANY WARRANTY; without even the implied warranty of
         MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
         GNU General Public License for more details.

         You should have received a copy of the GNU General Public License
         along with Foobar; if not, write to the Free Software
         Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
  */session_start();
	include ("../../includes/include_geral.inc.php");
	include ("../../includes/include_geral_II.inc.php");

	$_SESSION['s_page_invmon'] = $_SERVER['PHP_SELF'];

	$cab = new headers;
	$cab->set_title(TRANS("html_title"));

	$auth = new auth;
	$auth->testa_user($_SESSION['s_usuario'],$_SESSION['s_nivel'],$_SESSION['s_nivel_desc'],2);

	$hoje = date("Y-m-d H:i:s");


	$cor  = TD_COLOR;
	$cor1 = TD_COLOR;
	$cor3 = BODY_COLOR;

	$queryInst = "SELECT * from instituicao order by inst_nome";
	$resultadoInst = mysql_query($queryInst);
	$linhasInst = mysql_num_rows($resultadoInst);

		print "<div id='Layer2' style='position:absolute; left:80%; top:176px; width:15%; height:40%; z-index:2; '>";//  <!-- Ver: overflow: auto    n�o funciona para o Mozilla-->
			print "<b>Unidade:</font></font></b>";
			print "<FORM name='form1' method='post' action='".$_SERVER['PHP_SELF']."'>";
			$sizeLin = $linhasInst+1;
			print "<select style='background-color: ".$cor3."; font-family:tahoma; font-size:11px;' name='instituicao[]' size='".$sizeLin."' multiple='yes'>";


			print "<option value='-1' selected>TODAS</option>";
			while ($rowInst = mysql_fetch_array($resultadoInst))
			{
				print "<option value='".$rowInst['inst_cod']."'>".$rowInst['inst_nome']."</option>";
			}
			print "</select>";
			print "<br><input style='background-color: ".$cor1."' type='submit' class='button' value='Aplicar' name='OK'>";

			print "</form>";
		print "</div>";

		$saida="";
		if (isset ($_POST['instituicao'])) {
			for ($i=0; $i<count($_POST['instituicao']); $i++){
				$saida.= $_POST['instituicao'][$i].",";
			}
		}
		if (strlen($saida)>1) {
			$saida = substr($saida,0,-1);
		}

		$msgInst = "";
		if (($saida=="")||($saida=="-1")) {
			$clausula = "";
			$clausula2 = "";
			$msgInst = "TODAS";
		} else {
			$sqlA ="select inst_nome as inst from instituicao where inst_cod in (".$saida.")";
			$resultadoA = mysql_query($sqlA);
			while ($rowA = mysql_fetch_array($resultadoA)) {
				$msgInst.= $rowA['inst'].', ';
			}
			$msgInst = substr($msgInst,0,-2);

			$clausula = " and comp_inst in (".$saida.")";
			$clausula2 = " and comp_inst in (".$saida.") ";
		}

		$queryB = "SELECT count(*) from equipamentos  where comp_tipo_equip in (1,2) ".$clausula."";
		$resultadoB = mysql_query($queryB);
		$total = mysql_result($resultadoB,0);

		// Select para retornar a quantidade e percentual de equipamentos cadastrados no sistema
		$query= "select count(*) as qtd, t.tipo_nome as equipamento, t.tipo_cod as tipo, h.mdit_cod as tipo_hd,
				concat(h.mdit_fabricante,' ',h.mdit_desc_capacidade,'GB') as hd, concat(count(*)/".$total."*100,'%') as porcento from
				equipamentos, tipo_equip as t, modelos_itens as h where
				comp_tipo_equip = t.tipo_cod and comp_modelohd = h.mdit_cod and h.mdit_tipo=1 ".$clausula2."
				group by hd order by qtd desc, hd";

		$resultado = mysql_query($query);
		$linhas = mysql_num_rows($resultado);

		print "<TABLE border='0' cellpadding='5' cellspacing='0' align='left' width='80%' bgcolor='".$cor3."'>";
			print "<tr><td class='line'></TD></tr>";
			print "<tr><td class='line'></TD></tr>";
			print "<tr><td width='80%' align='center'><b>Quantidade de computadores por HD.<p>Unidade: ".$msgInst.".</p></b></td></tr>";

		print "<td class='line'>";
		print "<fieldset><legend>Computadores X HD</legend>";
		print "<TABLE border='0' cellpadding='5' cellspacing='0' align='center' width='80%' bgcolor='".$cor3."'>";
			print "<TR><TD bgcolor='".$cor3."'><b>Equipamento</TD><TD bgcolor='".$cor3."'><b>HD</TD><TD bgcolor='".$cor3."'><b>Quantidade</TD><TD bgcolor='".$cor3."'><b>Percentual</TD></tr>";

		$i=0;
		$j=2;
		$totalFull = 0;

		while ($row = mysql_fetch_array($resultado)) {
			$color =  BODY_COLOR;
			$totalFull+=$row['qtd'];
			$j++;
			print "<TR>";
			print "<TD bgcolor='".$color."'>".$row['equipamento']."</TD>";
			print "<TD bgcolor='".$color."'><a href='mostra_consulta_comp.php?comp_tipo_equip=".$row['tipo']."&comp_modelohd=".$row['tipo_hd']."&ordena=local,etiqueta' title='Exibe a listagem de computadores cadastrados com esse modelo de HD.'>".$row['hd']."</a></TD>";
			print "<TD bgcolor='".$color."'>".$row['qtd']."</TD>";
			print "<TD bgcolor='".$color."'>".$row['porcento']."</TD>";
			print "</TR>";
			$i++;
		}

        	print "<TR><TD bgcolor='".$cor3."'><b></TD><TD bgcolor='".$cor3."'><b></TD><TD bgcolor='".$cor3."'><b>Total: <font color='red'>".$totalFull."</font></TD><TD bgcolor='".$cor3."'></TD></tr>";
		print "</TABLE>";
		print "</fieldset>";

		print "<TABLE width='80%' align='center'>";
		print "<tr><td class='line'></TD></tr>";
		print "<tr><td class='line'></TD></tr>";
		print "<tr><td class='line'></TD></tr>";
		print "<tr><td class='line'></TD></tr>";
		print "</TABLE>";

		print "<TABLE width='80%' align='center'>";
		print "<tr><td class='line'></TD></tr>";
		print "<tr><td class='line'></TD></tr>";
		print "<tr><td class='line'></TD></tr>";
		print "<tr><td class='line'></TD></tr>";

		print "<tr><td width='80%' align='center'><b>Sistema em desenvolvimento pelo setor de Helpdesk  do <a href='http://www.unilasalle.edu.br' target='_blank'>Unilasalle</a>.</b></td></tr>";
		print "</TABLE>";

print "</BODY>";
print "</HTML>";
?>