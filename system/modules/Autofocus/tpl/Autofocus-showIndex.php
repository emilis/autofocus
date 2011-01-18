<ul id="list-selector">
    <li class="active"><a href="#">Work</a></li>
    <li><a href="#">Home</a></li>
    <li><a href="#">Other</a></li>
</ul>
<div id="list">
    <div id="tasks">
        <h2>Tasks</h2>
        <table id="task-table"><tbody>
        </tbody>
        <tfoot>
            <tr id="add-task">
                <td colspan="2">
                    <small>Add new task:</small><br>
                    <input id="task-title" name="task-title"><br>
                    <input type="button" value="Add" onclick="autofocus.add()">
                </td>
            </tr>
        </tfoot></table>
    </div>
    <div id="log">
        <h2>Log</h2>
        <table id="log-table"><tbody>
            <tr>
                <td colspan="3"><h3>2011-01-18</h3></td>
            </tr>
            <tr>
                <td class="time">10:57</td>
                <td class="added">added</td>
                <td>Paskaičiuoti biudžetą hostingui</td>
            </tr>
            <tr>
                <td class="time">10:55</td>
                <td class="done">done</td>
                <td>Paskambinti Dariui</td>
            </tr>
        </tbody></table>
    </div>
</div>

