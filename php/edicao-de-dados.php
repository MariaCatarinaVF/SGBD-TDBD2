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

            $query_para_obter_valor_permitido = 'SELECT subitem_allowed_value.subitem_id, subitem_allowed_value.state, subitem_allowed_value.value
                                          FROM subitem_allowed_value,subitem
                                          WHERE subitem_allowed_value.subitem_id=subitem.id AND subitem_allowed_value.id='.$idValorPermitido.'';
            $conectar_query_para_obter_valor_permitido = mysqli_query($link,$query_para_obter_valor_permitido);
            $devolve_numero_linhas_valor_permitido = mysqli_num_rows($conectar_query_para_obter_valor_permitido);

                if ($imprime_subitens = $conectar_query_para_obter_valor_permitido->fetch_assoc())
                {
                    $idSubitem = $imprime_subitens['subitem_id'];
                    $estado = $imprime_subitens['state'];

                    echo '<div class="" >';//criar uma classe depois
                    echo '<h4>Pretende desativar o item?</h4>';
                    echo '<form method="post" action="">';
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
                    echo '<input type="hidden" name="tipo" value="' . $value . '">';
                    echo '<input type="hidden" name="id" value="' . $idValorPermitido . '">';
                    echo "<input type='hidden' name='estado' value='desativar_sucesso'>";
                    echo "<input type='submit' value='Submeter'>";
                    echo '</form>';
                    echo '<br>';
                    voltarAtras();
                }
        }
        else if($_REQUEST['estado'] == "desativar_sucesso")
        {
            $idValorPermitido = $_REQUEST['id'];

            $query_para_desativar = 'UPDATE subitem_allowed_value SET state="inactive" 
                                     WHERE id='.$idValorPermitido.'';
            $conectar_query_para_desativar = mysqli_query($link,$query_para_desativar);

            if ($conectar_query_para_desativar)
            {
                echo '<h4>Atualizações realizadas com sucesso</h4>';
                echo '<a href="/sgbd/gestao-de-valores-permitidos">Continuar</a>';
            }
            else
            {
                echo '<p>Ocorreu algum erro ao desativar o valor permitido '.mysqli_error($conectar_query_para_desativar).'</p>';
            }
        }

        if ($_REQUEST['estado'] == "ativar")
        {
            $idValorPermitido = $_GET['id'];
            $value = $_GET['tipo'];

            $query_para_obter_valor_permitido = 'SELECT subitem_allowed_value.subitem_id, subitem_allowed_value.state, subitem_allowed_value.value
                                          FROM subitem_allowed_value,subitem
                                          WHERE subitem_allowed_value.subitem_id=subitem.id AND subitem_allowed_value.id='.$idValorPermitido.'';
            $conectar_query_para_obter_valor_permitido = mysqli_query($link,$query_para_obter_valor_permitido);
            $devolve_numero_linhas_valor_permitido = mysqli_num_rows($conectar_query_para_obter_valor_permitido);

            if ($imprime_subitens = $conectar_query_para_obter_valor_permitido->fetch_assoc())
            {
                $idSubitem = $imprime_subitens['subitem_id'];
                $estado = $imprime_subitens['state'];

                echo '<div class="" >';//criar uma classe depois
                echo '<h4>Pretende ativar o item?</h4>';
                echo '<form method="post" action="">';
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
                echo '<input type="hidden" name="tipo" value="' . $value . '">';
                echo '<input type="hidden" name="id" value="' . $idValorPermitido . '">';
                echo "<input type='hidden' name='estado' value='ativar_sucesso'>";
                echo "<input type='submit' value='Submeter'>";
                echo '</form>';
                echo '<br>';
                voltarAtras();
            }
        }
        else if($_REQUEST['estado'] == "ativar_sucesso")
        {
            $idValorPermitido = $_REQUEST['id'];

            $query_para_ativar = 'UPDATE subitem_allowed_value SET state="active" 
                                     WHERE id='.$idValorPermitido.'';
            $conectar_query_para_ativar = mysqli_query($link,$query_para_ativar);

            if ($conectar_query_para_ativar)
            {
                echo '<h4>Atualizações realizadas com sucesso</h4>';
                echo '<a href="/sgbd/gestao-de-valores-permitidos">Continuar</a>';
            }
            else
            {
                echo '<p>Ocorreu algum erro ao ativar o valor permitido '.mysqli_error($conectar_query_para_ativar).'</p>';
            }
        }

        else if ($_REQUEST['estado'] == "editar")
        {
            $idValorPermitido = $_GET['id'];
            $value = $_GET['tipo'];

            $query_para_obter_valor_permitido = 'SELECT subitem_allowed_value.subitem_id, subitem_allowed_value.state, subitem_allowed_value.value
                                          FROM subitem_allowed_value,subitem
                                          WHERE subitem_allowed_value.subitem_id=subitem.id AND subitem_allowed_value.id='.$idValorPermitido.'';
            $conectar_query_para_obter_valor_permitido = mysqli_query($link,$query_para_obter_valor_permitido);
            $devolve_numero_linhas_valor_permitido = mysqli_num_rows($conectar_query_para_obter_valor_permitido);

            if ($imprime_subitens = $conectar_query_para_obter_valor_permitido->fetch_assoc())
            {
                $idSubitem = $imprime_subitens['subitem_id'];
                $estado = $imprime_subitens['state'];

                $query_obter_subitens = 'SELECT id FROM subitem WHERE subitem.value_type="enum';
                $conectar_query_para_obter_subitens = mysqli_query($link,$query_obter_subitens);

                echo '<div class="" >';//criar uma classe depois
                echo '<form method="post" action="">';
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
                echo '<td> <input type="text" name="tipo" value="' . $value . '"> </td>';
                echo '<td>' . $estado . '</td>';
                echo '</tr>';
                echo '</table>';
                echo '</div>';
                echo '</div>';
                echo '<input type="hidden" name="tipo" value="' . $value . '">';
                echo '<input type="hidden" name="id" value="' . $idValorPermitido . '">';
                echo "<input type='hidden' name='estado' value='editar_valor'>";
                echo "<input type='submit' value='Submeter'>";
                echo '</form>';
                echo '<br>';
                voltarAtras();
            }
        }
        else if ($_REQUEST['estado'] == "apagar")
        {
            echo '<h3>Estamos prestes a apagar os dados abaixo da base de dados. Confirma que pretende apagar os mesmos?</h3>';
            $idValorPermitido = $_GET['id'];
            $value = $_GET['tipo'];

            $query_para_obter_valor_permitido = 'SELECT subitem_allowed_value.subitem_id, subitem_allowed_value.state, subitem_allowed_value.value
                                          FROM subitem_allowed_value,subitem
                                          WHERE subitem_allowed_value.subitem_id=subitem.id AND subitem_allowed_value.id='.$idValorPermitido.'';
            $conectar_query_para_obter_valor_permitido = mysqli_query($link,$query_para_obter_valor_permitido);
            $devolve_numero_linhas_valor_permitido = mysqli_num_rows($conectar_query_para_obter_valor_permitido);



            if ($imprime_subitens = $conectar_query_para_obter_valor_permitido->fetch_assoc())
            {
                $idSubitem = $imprime_subitens['subitem_id'];
                $estado = $imprime_subitens['state'];

                echo '<div class="" >';//criar uma classe depois
                echo '<form method="post" action="">';
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
                echo '<input type="hidden" name="tipo" value="' . $value . '">';
                echo '<input type="hidden" name="id" value="' . $idValorPermitido . '">';
                echo "<input type='hidden' name='estado' value='apagar_valor'>";
                echo "<input type='submit' value='Submeter'>";
                echo '</form>';
                echo '<br>';
                voltarAtras();
            }
        }
        else if($_REQUEST['estado'] == "apagar_valor")
        {
            $idValorPermitido = $_REQUEST['id'];
            $query_para_eliminar_valor_permitido = 'DELETE FROM subitem_allowed_value WHERE id= ' .$idValorPermitido.'';
            $conectar_query_para_eliminar_valor_permitido = mysqli_query($link,$query_para_eliminar_valor_permitido);

            if($conectar_query_para_eliminar_valor_permitido)
            {
                echo '<h4>Eliminações realizadas com sucesso</h4>';
                echo '<a href="/sgbd/gestao-de-valores-permitidos">Continuar</a>';
            }
            else
            {
                echo '<p>Ocorreu algum erro ao eliminar o valor permitido '.mysqli_error($conectar_query_para_eliminar_valor_permitido).'</p>';
            }
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