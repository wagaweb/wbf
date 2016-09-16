#WBF Hooks Hierarchy

- WP: after_setup_theme (10)
    - WBF: after_setup_theme (11)
        - WBF: init notice manager
        - WBF: apply_filters: wbf/options
        - WBF: loads modules <------------------- ! (every modules bootstrap.php)
        - WBF: do_action: after_setup_theme
        - WBF: init wbf startup options
        - WBF: loads extensions  <------------------- ! (every extensions bootstrap.php)
        
- WP: init (10)
    - WBF: init (11)
        - WBF: do_action: wbf_init
        - WBF: enqueue notices