<?php
require_once("custom/php/common.php");
include'custom/css/ag.css';

if(isset($_REQUEST['origem']) != "")
{
    if ($_REQUEST['origem'] == "gestao-de-unidades")
    {
        if ($_REQUEST['estado'] == "apagar")
        {
            echo '<h3>Estamos prestes a apagar os dados abaixo da base de dados. Confirma que pretende apagar os mesmos?</h3>';
            $id_unidade = $_GET['id'];
            $unidade = $_GET['tipo'];
            echo '<table>';
            echo '<tr>';
            echo '<th>id</th>';
            echo '<th>nome</th>';
            echo '</tr>';
            echo '<tr>';
            echo '<td>' . $id_unidade . '</td>';
            echo '<td>' . $unidade . '</td>';
            echo '</tr>';
            echo '</table>';

            echo '<form method="post" action="">';
            echo '<input type="hidden" name="' . $unidade . '" value="' . $unidade . '">';
            echo '<input type="hidden" name="' . $id_unidade . '" value="' . $id_unidade . '">';
            echo '<input type="hidden" name="estado" value="apagar_sucesso">';
            echo '<input type="submit" value="Apagar">';
            echo '</form>';

            voltarAtras();
        }
        else if ($_REQUEST['estado'] == "apagar_sucesso")
        {
            $id_unidade = $_REQUEST['id'];

            //início de transação
            mysqli_begin_transaction($link);

            //update do id da unidade nos subitens que estavam associados
            $query_definir_nulo = 'UPDATE subitem SET unit_type_id = NULL WHERE unit_type_id = ' . $id_unidade;
            $result_nulo = mysqli_query($link, $query_definir_nulo);

            if ($result_nulo === false)
            {
                // Rolback em caso de erro
                mysqli_rollback($link);
                echo '<p>Ocorreu algum erro ao definir nulo nos subitens: ' . mysqli_error($link) . '</p>';
            }
            else
            {
                //apaga a unidade
                $query_apagar_unidades = 'DELETE FROM subitem_unit_type WHERE subitem_unit_type.id = ' . $id_unidade;
                $result_unidades = mysqli_query($link, $query_apagar_unidades);

                if ($result_unidades === false)
                {
                    // Rolback em caso de erro
                    mysqli_rollback($link);
                    echo '<p>Ocorreu algum erro ao eliminar a unidade: ' . mysqli_error($link) . '</p>';
                }
                else
                {
                    // Confirmar transação
                    mysqli_commit($link);
                    echo '<h4>Eliminações realizadas com sucesso</h4>';
                    echo '<a href="/sgbd/gestao-de-unidades">Continuar</a>';
                }
            }
        }
        else if ($_REQUEST['estado'] == "editar")
        {
            $id_unidade = $_GET['id'];
            $unidade = $_GET['tipo'];

            echo '<form method="post" action="">';
            echo '<table>';
            echo '<tr>';
            echo '<th>id</th>';
            echo '<th>nome</th>';
            echo '</tr>';
            echo '<tr>';
            echo '<td>' . $id_unidade . '</td>';
            echo '<td><input type="text" name="novo_nome_para_unidade" value="' . $unidade . '"></td>';
            echo '</tr>';
            echo '</table>';
            echo "<input type='hidden' name='id' value='$id_unidade'>";
            echo "<input type='hidden' name='estado' value='confirmar'>";
            echo "<input type='submit' value='Submeter'>";
            echo '</form>';

            voltarAtras();
        }
        else if ($_REQUEST['estado'] == "confirmar")
        {
            $id_unidade = $_REQUEST['id'];
            $novo_nome_para_unidade = $_REQUEST['novo_nome_para_unidade'];
            $query_para_editar_unidade = 'UPDATE subitem_unit_type SET name="' . $novo_nome_para_unidade . '" WHERE id="' . $id_unidade . '"';
            $conectarBD_query_para_editar_unidade = mysqli_query($link, $query_para_editar_unidade);

            if ($conectarBD_query_para_editar_unidade)
            {
                echo '<h4>Atualizações realizadas com sucesso</h4>';
                echo '<a href="/sgbd/gestao-de-unidades">Continuar</a>';
            }
            else
            {
                echo '<p>Ocorreu algum erro ao editar a unidade ' . mysqli_error($conectarBD_query_para_editar_unidade) . '</p>';
            }
            echo '<br>';
            voltarAtras();
        }
    }
    else if ($_REQUEST['origem'] == "gestao-de-valores-permitidos")
    {
        if ($_REQUEST['estado'] == "desativar")
        {
            $idValorPermitido = $_GET['id'];
            $value = $_GET['tipo'];
            $estado = $_GET['estado'];
            $idSubitem = $_GET['idSubitem'];
            echo '<div class="" >';//criar uma classe depois
            echo '<h4>Pretende desativar o item?</h4>';
            echo '<div class= "">';
            echo '<table>';
            echo '<tr>';
            echo '<th>id</th>';
            echo '<th>subitem_id</th>';
            echo '<th>value</th>';
            echo '<th>state</th>';
            echo '</tr>';
            echo '<tr>';
            echo '<td>' . $idValorPermitido . '</td>';
            echo '<td>' . $idSubitem . '</td>';
            echo '<td>' . $value . '</td>';
            echo '<td>' . $estado . '</td>';
            echo '</tr>';
            echo '</table>';
            echo '</div>';
            echo '</div>';
        }
        else if ($_REQUEST['estado'] == "editar")
        {
            echo '<p>Estou na página edição de dados para editar o valor permitido</p>';
        }
        else if ($_REQUEST['estado'] == "apagar")
        {
            echo '<p>Estou na página edição de dados para apagar o valor permitido</p>';
        }
    }
    else
    {
        echo '<p>Não foi selecionado nada para editar! Vá a uma das outras páginas e clique em editar, desativar e/ou apagar.</p>';
    }
}
else
{
    echo '<p>Não tem página de origem.</p>';
}
?>