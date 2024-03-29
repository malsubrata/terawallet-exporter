<?php
/**
 * Admin View: Product Export
 *
 * @package WooCommerce\Admin\Export
 */
if (!defined('ABSPATH')) {
    exit;
}
wp_enqueue_script('selectWoo');
wp_enqueue_script('terawallet-exporter-script');
$exporter = new TeraWallet_CSV_Exporter();
?>
<div class="wrap">
    <h1><?php esc_html_e('Export Transactions', 'woocommerce'); ?></h1>
    <div class="terawallet-exporter-wrapper">
        <form class="terawallet-exporter">
            <header>
                <span class="spinner is-active"></span>
                <h2><?php esc_html_e('Export transactions to a CSV file', 'woocommerce'); ?></h2>
                <p><?php esc_html_e('This tool allows you to generate and download a CSV file containing a list of all transactions.', 'woocommerce'); ?></p>
            </header>
            <section>
                <table class="form-table woocommerce-exporter-options">
                    <tbody>
                        <tr>
                            <th scope="row">
                                <label for="terawallet-exporter-columns"><?php esc_html_e('Which columns should be exported?', 'woocommerce'); ?></label>
                            </th>
                            <td>
                                <select id="terawallet-exporter-columns" name="terawallet-exporter-columns" class="terawallet-exporter-columns wc-enhanced-select" style="width:100%;" multiple data-placeholder="<?php esc_attr_e('Export all columns', 'woocommerce'); ?>">
                                    <?php
                                    foreach ($exporter->get_default_column_names() as $column_id => $column_name) {
                                        echo '<option value="' . esc_attr($column_id) . '">' . esc_html($column_name) . '</option>';
                                    }
                                    ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="terawallet-exporter-users"><?php esc_html_e('Which users should be exported?', 'woocommerce'); ?></label>
                            </th>
                            <td>
                                <select id="terawallet-exporter-users" name="terawallet-exporter-users" class="terawallet-exporter-users" style="width:100%;" multiple data-placeholder="<?php esc_attr_e('Export all users', 'woocommerce'); ?>"></select>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="terawallet-exporter-from-date"><?php esc_html_e('From date', 'woocommerce'); ?></label>
                            </th>
                            <td>
                                <input type="date" id="terawallet-exporter-from-date" name="terawallet-exporter-from-date" style="width: 100%" class="terawallet-exporter-from-date" />
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="terawallet-exporter-to-date"><?php esc_html_e('To date', 'woocommerce'); ?></label>
                            </th>
                            <td>
                                <input type="date" id="terawallet-exporter-to-date" name="terawallet-exporter-to-date" style="width: 100%" class="terawallet-exporter-to-date" />
                            </td>
                        </tr>
                    </tbody>
                </table>
                <progress class="terawallet-exporter-progress" max="100" value="50"></progress>
            </section>
            <div class="tw-actions">
                <button type="submit" class="terawallet-exporter-button button button-primary" value="<?php esc_attr_e('Generate CSV', 'woocommerce'); ?>"><?php esc_html_e('Generate CSV', 'woocommerce'); ?></button>
            </div>
        </form>
    </div>
</div>