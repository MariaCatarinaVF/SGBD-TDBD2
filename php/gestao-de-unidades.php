<?php
require_once("custom/php/common.php");
//require_once ("htdocs/custom/css/ag.css");
include'custom/css/ag.css';

if (!is_user_logged_in() || !current_user_can("manage_unit_types"))
{

    echo "Não tem autorização para aceder a esta página.";

}
else if(isset($_REQUEST['estado']) == "")
{
    echo'<table>';
    echo'<tbody>';
    echo '<thead >';
    echo'<th class="table-header">id</th>';
    echo'<th class="table-header">unidade</th>';
    echo'<th class="table-header">subitem</th>';
    echo'<th class="table-header">ação</th>';
    echo '</thead>';
    $query_para_obter_id_unidade ='SELECT id, name AS unidade FROM subitem_unit_type ORDER BY id';
    $conexao_query_BD = mysqli_query($link,$query_para_obter_id_unidade);
    $devolve_numero_linhas = mysqli_num_rows($conexao_query_BD);

    if($devolve_numero_linhas <= 0)
    {
        echo '<td>Não há tipos de unidades</td>';
    }
    else
    {
        while($imprime_valores_tabela = $conexao_query_BD -> fetch_assoc()) //percorre as linhas da base de dados
        {
            $id_unidade = $imprime_valores_tabela['id'];
            $unidade = $imprime_valores_tabela['unidade'];
            echo '<tr>';
            echo '<td>'.$id_unidade.'</td>';
            echo '<td>'.$unidade.'</td>';

            $query_para_obter_SubitemNome_ItemNome = 'SELECT subitem.name AS nome_subitem,item.name AS nome_item
                                                        FROM subitem_unit_type
                                                        INNER JOIN subitem ON subitem.unit_type_id ='.$id_unidade.'
                                                        INNER JOIN item ON subitem.item_id = item.id
                                                        GROUP BY subitem.id';
            $conexao_query_BD_acima = mysqli_query($link,$query_para_obter_SubitemNome_ItemNome);
            $devolve_numero_linhas_acima = mysqli_num_rows($conexao_query_BD_acima);

            if($devolve_numero_linhas_acima <= 0)
            {
                echo '<td>Não há subitens</td>';
            }
            else
            {
                echo '<td>';

                $subitem_nomes = array();
                while($imprime_restantes_valores_tabela = $conexao_query_BD_acima -> fetch_assoc())
                {
                    $subitem_nome = $imprime_restantes_valores_tabela['nome_subitem'];
                    $item_nome = $imprime_restantes_valores_tabela['nome_item'];
                    $subitem_nomes[] = $subitem_nome . " (" . $item_nome . ")"; //guarda no array o subitem nome e o nome do item
                }
                echo $subitem_nomes_por_unidade[$id_unidade] = implode(", " ,$subitem_nomes); //retorna o array subitem_nomes separado por vírgulas pelo id_unidade

                echo'</td>';
            }
            echo'<td>';
            echo "<a href='/sgbd/edicao-de-dados?estado=editar&tipo=" . $unidade . "&id=" . $id_unidade . "&origem=gestao-de-unidades'>[editar]</a>";
            echo "<a href='/sgbd/edicao-de-dados?estado=apagar&tipo=" . $unidade . "&id=" . $id_unidade . "&origem=gestao-de-unidades'>[apagar]</a>";
            echo'</td>';
            echo '</tr>';
        }
    }
    echo '</tbody>';
    echo '</table>';

    echo'<h3>Gestão de unidades - introdução</h3>';
    echo '<div class="formulario">';
    echo'<form action = "" method="post">';
    echo'<label for="nome_para_unidade" class="nome_unidade">Nome</label>';
    echo'<input type="text" id="nome_para_unidade" name="nome_para_unidade">';
    echo'<input type="hidden" name="estado" value="inserir">';
    echo'<br><br>';
    echo'<input type="submit" class = "inserir_tipo_unidade" value="Inserir tipo de unidade">';
    echo'</form>';
    echo'</div>';
}
else if($_REQUEST['estado'] == "inserir")
{
    echo'<h3 class="insercao_unidades">Gestão de unidades - inserção</h3>';

    $nome_para_unidade = $link -> real_escape_string($_REQUEST['nome_para_unidade']);

    if(empty($nome_para_unidade))
    {
        echo'<div class="formulario campo_obrigatorio">';
        echo'<p>Campo obrigatório!</p>';
        voltarAtras();
        echo'</div>';
    }
    else if((!preg_match("/^[a-zA-Z-Ç-ç\/á-úÁ-Úâ-ûÂ-Ûã-õÃ-Õä-üÄ-Ü]+$/", $nome_para_unidade)))
    {
        echo'<div class="formulario campo_obrigatorio">';
        echo'<p class="campo_obrigatorio">Este campo deve incluir apenas letras, acentos e/ou "/"!</p>';
        echo'<div class="voltar_atras">'.voltarAtras().'</div>';
        echo'</div>';
    }
    else
    {
        $gestao_unidades = paginaAtual();
        $inserir_nome_para_unidade = "INSERT INTO subitem_unit_type (name) VALUES ('$nome_para_unidade')";
        $conectar_inserir_nome_para_unidade_com_BD = mysqli_query($link,$inserir_nome_para_unidade);
        if($conectar_inserir_nome_para_unidade_com_BD)
        {
            echo'<div class="formulario inseridoComSucesso">';
            echo'<p class="inserido_com_sucesso">Inseriu os dados de novo tipo de unidade com sucesso.</p>';
            echo'<p class="inserido_com_sucesso">Clique em <a href="'.$gestao_unidades.'">Continuar</a> para avançar</p>';
            echo'</div>';
        }
        else
        {
            echo'<p>Ocorreu algum erro na inserção do nome da unidade</p>';
        }
    }
}
?>
