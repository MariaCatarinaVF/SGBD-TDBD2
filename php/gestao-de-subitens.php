<?php
require_once("custom/php/common.php");
global $connection, $current_page;

echo '<form method="post" action="'.$current_page.'">';

if (is_user_logged_in() && current_user_can("manage_subitems")) 
{
    if ($_REQUEST['estado'] == '') 
    {
        echo "<table style =´width:100%'>";
        echo '<thread>';
        echo "<tr>
                <th>item</th>
                <th>id</th>
                <th>subitem</th>
                <th>tipo de valor</th>
                <th>nome do campo no formulario</th>
                <th>tipo de campo no formulario</th>
                <th>tipo de unidade</th>
                <th>ordem do campo no formulario</th>
                <th>obrigatório</th>
                <th>estado</th>
                <th>ação</th>
            </tr>";
        echo '<thead>';
        $items = "SELECT name, id
                FROM item
                ORDER BY item.name ASC";
        $itemResult = mysqli_query($link, $items);

        echo '<tbody>';
        foreach ($itemResult as $it) 
        {
            echo '<tr>';

            $subitems = "SELECT subitem.id, subitem.item_id,  subitem.name, subitem.value_type, subitem.form_field_name, subitem.form_field_type, 
            subitem_unit_type.name as Unit, subitem.form_field_order, subitem.mandatory, subitem.state
                    FROM subitem, subitem_unit_type,item
                    WHERE subitem_unit_type.id = subitem.unit_type_id
                    AND subitem.item_id = " . $it['id'] . "
                    GROUP BY subitem.id
                    ORDER BY item.name ASC";
            $resultsub = mysqli_query($link, $subitems);

            $numRows = mysqli_num_rows($resultsub);
            $currentRow = 0;
            $lastRow = '';
            
            foreach ($resultsub as $subs) 
            {
                if ($lastRow != $it['name']) 
                {
                    $lastRow = $it['name'];
                    $currentRow = 0;
                }
                else 
                {
                    echo '<tr>';
                    if ($currentRow == 0) 
                    {
                        echo '<td rowspan="' . $numRows . '">' . $it['name'] . '</td>';
                    }
                    $currentRow++;
                    echo '<td>' . $subs['id'] . '</td>';
                    echo '<td>' . $subs['name'] . '</td>';
                    echo '<td>' . $subs['value_type'] . '</td>';
                    echo '<td>' . $subs['form_field_name'] . '</td>';
                    echo '<td>' . $subs['form_field_type'] . '</td>';
                    if ($subs['Unit'] == null) 
                    {
                        echo '<td>' . $subs['Unit'] . '</td>';
                    } 
                    else 
                    {
                        echo '<td>-</td>';
                    }
                    echo '<td>' . $subs['form_field_order'] . '</td>';
                    if ($subs['mandatory'] == 1) 
                    {
                        echo '<td>sim</td>';
                    }
                    else 
                    {
                        echo '<td>não</td>';
                    }
                    echo '<td>' . $subs['state'] . '</td>';
                    echo '<td><a href="">[editar]</a><a href="">[desativar]</a><a href="">[apagar]</a>';
                    echo '</tr>';
                }
            }
            if ($numRows == 0) 
            {
                echo '<tr>';
                echo '<td>' . $it['name'] . '</td>';
                echo '<td style="text-align: center" colspan="10">este item não tem subitens</td>';
                echo '</tr>';
            }
        }
        echo '</tbody>';
        echo "</table>";

        echo '<div class="formulario_registo">';
        echo '<strong>Dados de registro - introdução</strong>
                <br>
                <br>
                <strong class="vermelho">* Obrigatório</strong>
                <br>
                <form method="post" action="">
                <label for="name"><strong>Nome do subitem: </strong><strong class="vermelho">*</strong></label>
                <input type="text" name="nameSub">
                <strong>Tipo de valor</strong><strong class="vermelho">*</strong>
                <br>
                <input type="radio" name="tipoValor" value="text">
              <label for="text">text</label>
              <input type="radio" name="tipoValor" value="bool">
              <label for="bool">bool</label>
              <input type="radio" name="tipoValor" value="int">
              <label for="int">int</label>
              <input type="radio" name="tipoValor" value="double">
              <label for="double">double</label>
              <input type="radio" name="tipoValor" value="enum">
              <label for="enum">enum</label>
              <br>
			  <label for="item"><strong>Item: </strong><strong class="vermelho">*</strong></label> 
			  <select name="item">
			  <option value=""></option>
			  ';
        $items = "SELECT name, id
                FROM item
                ORDER BY item.name ASC";
        $itemResult = mysqli_query($link, $items);

        while ($itens = mysqli_fetch_assoc($itemResult)) 
        {
            echo '<option value="' . $itens['id'] . '">' . $itens['name'] . '</option>';
        }
        echo '</select>
			  <strong>Tipo de campo do formulário</strong><strong class="vermelho">*</strong>
			  <br>
              <input type="radio" name="tipoCampo" value="text">
              <label for="text">text</label>
              <input type="radio" name="tipoCampo" value="texbox">
              <label for="texbox">texbox</label>
              <input type="radio" name="tipoCampo" value="radio">
              <label for="radio">radio</label>
              <input type="radio" name="tipoCampo" value="checkbox">
              <label for="checkbox">checkbox</label>
              <input type="radio" name="tipoCampo" value="selectbox">
              <label for="selectbox">selectbox</label>
              <br>
              <select name="subitemUnit">
              <option value=""></option>
			  ';
        $tipoUni = "SELECT name, id
                            FROM subitem_unit_type
                            ORDER BY subitem_unit_type.name ASC";
        $tipoResult = mysqli_query($link, $tipoUni);

        while ($tipoUni = mysqli_fetch_assoc($tipoResult)) 
        {
            echo '<option value="' . $tipoUni['id'] . '">' . $tipoUni['name'] . '</option>';
        }
        echo '</select>';
        echo '<label for="ordemSub"><strong>Ordem do campo no formulário: </strong><strong class="vermelho">*</strong></label>';
        echo '<input type="text" name="ordemSub">';
        echo '<strong>Obrigatório: </strong><strong class="vermelho">*</strong>';
        echo '<br>';
        echo '<input type="radio" name="mandatory" value="1">';
        echo '<label for="sim">Sim</label>';
        echo '<input type="radio" name="mandatory" value="0">';
        echo '<label for="nao">Não</label>';
        echo '<br>';
        echo '<input type="hidden" name="estado" value="validar"/>';
        echo '<input type="submit" name="submeter" value="SUBMETER"/>';
        echo '</form>';
        echo '</div>';

    } 
    else if ($_REQUEST['estado'] == 'validar') 
    {
        $errors = false;
        $name = htmlspecialchars($_REQUEST['nameSub']);
        $tipoValor = $_REQUEST['tipoValor'];
        $item = $_REQUEST['item'];
        $tipoCampo = $_REQUEST['tipoCampo'];
        $subUnit = $_REQUEST['subitemUnit'];
        $subOrdem = htmlspecialchars($_REQUEST['ordemSub']);
        $mandatory = $_REQUEST['mandatory'];

        if (empty($name)) 
        {
            echo "É necessario inserir o nome do subitem.<br>";
            $errors = true;
        }
        if (!empty($name) && !preg_match("/^[\p{L}\s]*$/u", $name)) 
        {
            echo "O nome do subitem contém caracteres inválidos.<br>";
            $errors = true;
        }
        if (empty($tipoValor)) 
        {
            echo "É necessario selecionar um tipo de valor.<br>";
            $errors = true;
        }
        if (empty($item)) 
        {
            echo "É necessario selecionar o item a qual este subitem pertence.<br>";
            $errors = true;
        }
        if (empty($tipoCampo)) 
        {
            echo "É necessario adicionar o tipo de campo deste subitem.<br>";
            $errors = true;
        }
        if (empty($subOrdem)) 
        {
            echo "É necessario indicar a ordem do subitem no formulário.<br>";
            $errors = true;
        }
        if (!empty($subOrdem) && !is_numeric($subOrdem)) 
        {
            echo "A ordem do subitem deve apenas conter números.<br>";
            $errors = true;
        }
        if (empty($mandatory)) 
        {
            echo "Deve selecionar se o subitem é obrigatório.<br>";
            $errors = true;
        }
        if ($errors) 
        {
            voltarAtras();
        }

        if (!$errors) 
        {
            $insert = "INSERT INTO subitem (name, value_type, item_id, form_field_type, form_field_order, mandatory) VALUES ('" . 
            $name . "', '" . $tipoValor . "', '" . $item . "', '" . $tipoCampo . "', '"
                . $subOrdem . "', '" . $mandatory . "')";

            if (mysqli_query($link, $insert)) 
            {
                echo "<h5>Gestão de subitens - inserção</h5>";
                echo "<h5>Inseriu os dados de novo subitem com sucesso.</h5>";
                echo '<form method="post" action="">';
                echo '<input type="hidden" name="estado" value="">';
                echo '<input type="submit" value="CONTINUAR">';
                echo '</form>';
            } 
            else 
            {
                echo "Ocorreu um erro ao inserir os dados.";
                echo "<br>";
                voltarAtras();
            }
        }
    }
}
else 
{
    echo "Não tem autorização para aceder a esta página";
}
include'custom/css/ag.css';
?>