<?php
require_once("custom/php/common.php");
include'custom/css/ag.css';

if (!is_user_logged_in() || !current_user_can("manage_allowed_values"))
{

    echo "Não tem autorização para aceder a esta página.";

}
else if(isset($_REQUEST['estado']) == "")
{
    echo'<table>';
    echo '<tbody>';
    echo'<th>item</th>';
    echo'<th>id</th>';
    echo'<th>subitem</th>';
    echo'<th>id</th>';
    echo'<th>valores permitidos</th>';
    echo'<th>estado</th>';
    echo'<th>ação</th>';

    //obtem os itens todos
    $query_para_obter_id_nome_do_item = "SELECT name AS item_nome, id AS item_id FROM item";
    $conexao_query_para_obter_id_nome_do_item = mysqli_query($link, $query_para_obter_id_nome_do_item);
    $devolve_numero_linhas_query = mysqli_num_rows($conexao_query_para_obter_id_nome_do_item);

    while ($imprime_id_nome_do_item = $conexao_query_para_obter_id_nome_do_item -> fetch_assoc())
    {
        $nome_item = $imprime_id_nome_do_item['item_nome'];
        $id_item = $imprime_id_nome_do_item['item_id'];

        //obtem os subitens daquele item que tem enum
        $query_para_obter_id_nome_do_subitem = "SELECT subitem.id AS subitem_id, subitem.name AS subitem_nome, subitem.value_type AS subitem_value_type 
                                                        FROM subitem
                                                        WHERE subitem.item_id = $id_item AND subitem.value_type = 'enum'
                                                        ORDER BY subitem_id";
        $conexao_query_para_obter_id_nome_do_subitem = mysqli_query($link, $query_para_obter_id_nome_do_subitem);
        $devolve_numero_linhas_query_subitem = mysqli_num_rows($conexao_query_para_obter_id_nome_do_subitem);

        echo '<tr>';

        $conexao_query_para_obter_id_nome_do_subitem_para_Todos_Subitems_Tem_AllValue = mysqli_query($link,$query_para_obter_id_nome_do_subitem);

        $qtdValPerMaisQtdValNaoPer = 0;
        while ($imprime_id_nome_do_subitem = $conexao_query_para_obter_id_nome_do_subitem_para_Todos_Subitems_Tem_AllValue->fetch_assoc())
        {
            $nome_do_subitem = $imprime_id_nome_do_subitem['subitem_nome'];
            $id_subitem = $imprime_id_nome_do_subitem['subitem_id'];
            $value_type_subitem = $imprime_id_nome_do_subitem['subitem_value_type'];

            //obtem os valores permitidos daquele subitem
            $query_para_obter_id_do_subitemAllowedValue = "SELECT subitem_allowed_value.id
                                                                                FROM subitem_allowed_value,subitem
                                                                                WHERE subitem_allowed_value.subitem_id=subitem.id AND subitem_id=$id_subitem 
                                                                                GROUP BY subitem_allowed_value.value";//ver o subitem.id
            $conexao_query_para_obter_subitemAllowedValues = mysqli_query($link, $query_para_obter_id_do_subitemAllowedValue);
            $devolve_numero_linhas_query_subitemAllowedValue = mysqli_num_rows($conexao_query_para_obter_subitemAllowedValues);

            $qtdValPerMaisQtdValNaoPer += $devolve_numero_linhas_query_subitemAllowedValue;
        }

        //obtem os valores nao permitidos daquele item e subitem?
        $query_para_obter_subitems_sem_AllowedValue = "SELECT subitem.id
                                                                    FROM subitem
                                                                    WHERE subitem.item_id = $id_item AND subitem.value_type = 'enum' AND subitem.id
                                                                    NOT IN (SELECT subitem_id
                                                                            FROM subitem_allowed_value, subitem
                                                                            WHERE subitem_allowed_value.subitem_id = subitem.id AND subitem.value_type = 'enum')";
        $conexao_query_para_obter_subitems_sem_AllowedValue = mysqli_query($link, $query_para_obter_subitems_sem_AllowedValue);
        $devolve_numero_linhas_query_subitemSemAllowedValue = mysqli_num_rows($conexao_query_para_obter_subitems_sem_AllowedValue);

        $qtdValPerMaisQtdValNaoPer += $devolve_numero_linhas_query_subitemSemAllowedValue;

        if($devolve_numero_linhas_query_subitem > 0)
        {

            echo '<td colspan="1" rowspan='.$qtdValPerMaisQtdValNaoPer.'>' . $nome_item . '</td>';

            while($imprime_subitens = $conexao_query_para_obter_id_nome_do_subitem->fetch_assoc())
            {
                $idSubitem = $imprime_subitens['subitem_id'];
                $nomeSubitem = $imprime_subitens['subitem_nome'];

                $query_para_obter_nome_estado_do_subitemAllowedValue = "SELECT subitem_allowed_value.id,subitem_allowed_value.value,subitem_allowed_value.state 
                                                                                FROM subitem_allowed_value,subitem
                                                                                WHERE subitem_allowed_value.subitem_id=subitem.id AND subitem_id=$idSubitem 
                                                                                GROUP BY subitem_allowed_value.value";
                $conexao_query_para_obter_nome_estado_do_subitemAllowedValue = mysqli_query($link, $query_para_obter_nome_estado_do_subitemAllowedValue);
                $devolve_numero_linhas_query_subitemAllowedValue = mysqli_num_rows($conexao_query_para_obter_nome_estado_do_subitemAllowedValue);

                $gestao_valores_permitidos = paginaAtual();

                if($devolve_numero_linhas_query_subitemAllowedValue <= 0)
                {
                    echo '<td>' . $idSubitem . '</td>';
                    echo '<td>' . '[' . '<a href="' . $gestao_valores_permitidos . '?estado=introducao&subitem=' . $idSubitem . '">' . $nomeSubitem . '</a>' . ']' . '</td>';
                    echo '<td colspan="4" rowspan="1">Não há valores permitidos definidos</td>';
                    echo '</tr>';
                }
                else
                {
                    echo '<td colspan="1" rowspan="'.$devolve_numero_linhas_query_subitemAllowedValue.'">' . $idSubitem . '</td>';
                    echo '<td colspan="1" rowspan=" '.$devolve_numero_linhas_query_subitemAllowedValue.' ">' . '[' . '
                    <a href="' . $gestao_valores_permitidos . '?estado=introducao&subitem=' . $idSubitem . '">' . $nomeSubitem . '</a>' . ']' . '</td>';

                    while($imprime_nome_estado_subitemAllowedValue = $conexao_query_para_obter_nome_estado_do_subitemAllowedValue->fetch_assoc())
                    {
                        $idValorPermitido = $imprime_nome_estado_subitemAllowedValue['id'];
                        $value = $imprime_nome_estado_subitemAllowedValue['value'];
                        $estado = $imprime_nome_estado_subitemAllowedValue['state'];

                        echo '<td>'.$idValorPermitido.'</td>';
                        echo '<td>'.$value.'</td>';
                        echo '<td>'.$estado.'</td>';

                        if($estado == 'active')
                        {
                            echo '<td>';
                            echo "<a href='/sgbd/edicao-de-dados?estado=editar&tipo=" . $value . "&id=" . $idValorPermitido . "&idSubitem=" . $idSubitem . "&origem=gestao-de-valores-permitidos'>[editar]</a>";
                            echo "<a href='/sgbd/edicao-de-dados?estado=desativar&tipo=" . $value . "&id=" . $idValorPermitido . "&idSubitem=" . $idSubitem . "&state=" .$estado. "&origem=gestao-de-valores-permitidos'>[desativar]</a>";
                            echo "<a href='/sgbd/edicao-de-dados?estado=apagar&tipo=" . $value . "&id=" . $idValorPermitido . "&idSubitem=" . $idSubitem . "&state=" .$estado. "&origem=gestao-de-valores-permitidos'>[apagar]</a>";
                            echo '</td>';
                        }
                        else
                        {
                            echo '<td>';
                            echo "<a href='/sgbd/edicao-de-dados?estado=editar&tipo=" . $value . "&id=" . $idValorPermitido . "&idSubitem=" . $idSubitem . "&origem=gestao-de-valores-permitidos'>[editar]</a>";
                            echo "<a href='/sgbd/edicao-de-dados?estado=ativar&tipo=" . $value . "&id=" . $idValorPermitido . "&idSubitem=" . $idSubitem . "&state=" .$estado. "&origem=gestao-de-valores-permitidos'>[ativar]</a>";
                            echo "<a href='/sgbd/edicao-de-dados?estado=apagar&tipo=" . $value . "&id=" . $idValorPermitido . "&idSubitem=" . $idSubitem . "&state=" .$estado. "&origem=gestao-de-valores-permitidos'>[apagar]</a>";
                            echo '</td>';
                        }
                        echo '</tr>';
                    }
                }
            }

        }
    }
    echo '</tbody>';
    echo '</table>';
}
else if($_REQUEST['estado'] == 'introducao')
{

    $_SESSION['subitem_id'] = $_REQUEST['subitem'];
    $idSubitem = $_SESSION['subitem_id'];

    echo '<div class="formulario">';
    echo '<h3 class="valores_permitidos_introducao">Gestão de valores permitidos - introdução</h3>';

    echo'<form action = "" method="post">';
    echo'<label for="nome_para_valor_permitido" class="valor_para_valor_permitido">Valor</label>';
    echo'<input type="text" id="Valor" name="Valor"/>';
    echo'<input type="hidden" name="estado" value="inserir"/>';
    echo'<input type="hidden" name="subitem" value="'.$idSubitem.'"/>';
    echo'<br><br>';
    echo'<input type="submit" name="submit" value="Inserir valor permitido"/>';
    echo'</form>';
    echo '</div>';
    echo'<br>';
    voltarAtras();

}
else if($_REQUEST['estado'] == 'inserir')
{
    echo'<h3>Gestão de valores permitidos - inserção</h3>';

    $subitem_id = $_REQUEST['subitem'];
    $nome_para_valor_permitido_novo = $link -> real_escape_string($_REQUEST['Valor']);

    if(empty($nome_para_valor_permitido_novo))
    {
        echo'<p class="campo_obrigatorio">Campo obrigatório!</p>';
        voltarAtras();
    }
    else if((!preg_match("/^[a-zA-Z-Ç-ç]+$/",$nome_para_valor_permitido_novo)))
    {
        echo'<p class="campo_obrigatorio">Este campo deve incluir apenas letras!</p>';
        voltarAtras();
    }
    else
    {
        $query_para_inserir_valor_permitido_novo = "INSERT INTO subitem_allowed_value (subitem_id, value, state) 
                                                VALUES ('$subitem_id', '$nome_para_valor_permitido_novo', 'active')";
        $conectar_inserir_valor_permitido_novo =  mysqli_query($link,$query_para_inserir_valor_permitido_novo);

        if($conectar_inserir_valor_permitido_novo)
        {
            $gestao_valores_permitidos = paginaAtual();
            echo'<p>Inseriu os dados de novo valor permitido com sucesso.</p>';
            echo'<p>Clique em <a href="'.$gestao_valores_permitidos.'">Continuar</a> para avançar</p>';
        }
        else
        {
            echo'<p>Ocorreu algum erro de inserção do nome para o valor permitido</p>';
            voltarAtras();
        }
    }
}
?>
