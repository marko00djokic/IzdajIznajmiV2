# Матрица литературе

**Циљана провера: 23. октобар 2026.** OpenAlex `resolve_references` је упоредио
свих 24 записа у једном захтеву: 22 тачна поклапања, два без поклапања
(L1 и погрешан DOI у L19), без ознаке повлачења рада. После исправке
L19 DOI је такође тачно разрешен; L1 има институционални примарни запис. Semantic Scholar
`bulk_papers` је допунски проверио L10, L17 и L18; стари L19 DOI није
разрешен ни тамо. Странице издавача или институционалног репозиторијума
коришћене су за спорне и раније блокиране записе. Ниво приступа у последњој
колони означава *шта је стварно прочитано у овој провери*: пуни текст,
апстракт или само метаподаци. OpenAlex запис сам по себи није увид у рад;
из редова са само метаподацима не изводе се појединачни емпиријски налази.
Стари статус „DOI проверен“ није замена за проверу садржаја.

**Табела 1. Академски извори (извор: аутор).**

| ID | Извор | Тип | DOI/URL | Кључна тврдња | PIR поглавље | Статус провере |
|---|---|---|---|---|---|---|
| L1 | Gorry, G. A.; Scott Morton, M. S. (1971), *A Framework for Management Information Systems*, Sloan Management Review 13(1), 55–70 | DSS | https://sloanreview.mit.edu/article/a-framework-for-management-information-systems/ | DSS и полуструктурисане одлуке | 2.1 | Само метаподаци: MIT репозиторијум потврђује радну верзију 510-71; OpenAlex није разрешио часописни запис; библиографија часописа остаје за потврду |
| L2 | Keen, P. G. W.; Scott Morton, M. S. (1978), *Decision Support Systems: An Organizational Perspective*, Addison-Wesley | DSS књига | ISBN 9780201036676 | човек, модел и подаци | 2.1 | Само метаподаци: OpenAlex тачан наслов/година/аутори; његов venue је погрешно индексиран, па није коришћен |
| L3 | Saaty, T. L. (1977), *A Scaling Method for Priorities in Hierarchical Structures*, Journal of Mathematical Psychology 15(3), 234–281 | MCDM | 10.1016/0022-2496(77)90033-5 | AHP и приоритети | 2.1 | Апстракт: ScienceDirect потврђује парна поређења, својствени вектор и хијерархију |
| L4 | Hwang, C.-L.; Yoon, K. (1981), *Multiple Attribute Decision Making: Methods and Applications: A State-of-the-Art Survey*, Springer, LNEMS 186 | MCDM књига | 10.1007/978-3-642-48318-9 | TOPSIS/MADM | 2.1 | Издавачки опис и садржај: Springer потврђује поднаслов, серију и преглед MADM метода; поглавље о TOPSIS-у није прочитано |
| L5 | Triantaphyllou, E. (2000), *Multi-Criteria Decision Making Methods: A Comparative Study*, Springer | MCDM књига | 10.1007/978-1-4757-3157-6 | нормализација и поређење метода | 2.1/4 | Издавачки опис: Springer потврђује теоријско и емпиријско поређење метода; пуна књига није прочитана |
| L6 | Adomavicius, G.; Tuzhilin, A. (2005), *Toward the next generation of recommender systems: a survey of the state-of-the-art and possible extensions*, IEEE TKDE 17(6), 734–749 | survey | 10.1109/TKDE.2005.99 | класе recommender система | 2.2 | Апстракт: IEEE Xplore наводи content-based, collaborative и hybrid приступе и њихова ограничења; допуњен пуни наслов |
| L7 | Burke, R. (2002), *Hybrid Recommender Systems: Survey and Experiments*, User Modeling and User-Adapted Interaction 12, 331–370 | survey | 10.1023/A:1021240730564 | таксономија хибрида | 2.2 | Апстракт: Springer потврђује комбиновање више приступа и пример EntreeC |
| L8 | Burke, R. (2007), *Hybrid Web Recommender Systems*, у The Adaptive Web, 377–408 | преглед | 10.1007/978-3-540-72079-9_12 | начини комбиновања | 2.2 | Апстракт: Springer наводи седам стратегија хибридизације и 41 испитану комбинацију |
| L9 | Ricci, F.; Rokach, L.; Shapira, B. (ур., 2022), *Recommender Systems Handbook*, 3rd ed., Springer | приручник | 10.1007/978-1-0716-2197-4 | домен и евалуација | 2.2 | Издавачки опис и садржај: Springer потврђује издање и тематски опсег; поглавља нису прочитана |
| L10 | Zangerle, E.; Bauer, C. (2022), *Evaluating Recommender Systems: Survey and Framework*, ACM Computing Surveys 55(8), Article 170, 1–38 | survey | 10.1145/3556536 | вишедимензионална евалуација | 1.2/2.5 | Пуни текст: ауторска/универзитетска PDF верзија Универзитета у Инзбруку; OpenAlex DOI и Semantic Scholar; ACM страна враћа 403, али приступ раду је отклоњен |
| L11 | Knijnenburg, B. P. et al. (2012), *Explaining the User Experience of Recommender Systems*, User Modeling and User-Adapted Interaction 22, 441–504 | UX модел | 10.1007/s11257-011-9118-4 | објективни и субјективни конструкти | 1.2/2.3 | Апстракт: страница издавача Springer; раније блокирана провера отклоњена |
| L12 | Pu, P.; Chen, L.; Hu, R. (2011), *A User-Centric Evaluation Framework for Recommender Systems*, RecSys ’11, 157–164 | framework | 10.1145/2043932.2043962 | user-centric мере | 1.2/2.3 | Само метаподаци: OpenAlex DOI и EPFL ауторски списак; ACM страна враћа 403, EPFL PDF није доступан; садржај остаје непроверен |
| L13 | Tintarev, N.; Masthoff, J. (2012), *Evaluating the effectiveness of explanations for recommender systems: Methodological issues and empirical studies on the impact of personalization*, UMUAI 22, 399–439 | explainability | 10.1007/s11257-011-9117-5 | циљеви објашњења | 2.3 | Апстракт: страница издавача Springer; раније блокирана провера отклоњена, допуњен поднаслов |
| L14 | Tintarev, N.; Masthoff, J. (2007), *A Survey of Explanations in Recommender Systems*, ICDE Workshops, 801–810 | survey | 10.1109/ICDEW.2007.4401070 | таксономија објашњења | 2.3 | Само метаподаци: OpenAlex DOI |
| L15 | Herlocker, J. L.; Konstan, J. A.; Riedl, J. (2000), *Explaining Collaborative Filtering Recommendations*, CSCW ’00, 241–250 | експеримент | 10.1145/358916.358995 | објашњење и прихватање | 2.3 | Пуни текст: ауторски GroupLens PDF; у огледу је поређен 21 приказ исте препоруке код 78 корисника MovieLens-а |
| L16 | Bilgic, M.; Mooney, R. J. (2005), *Explaining Recommendations: Satisfaction vs. Promotion*, Beyond Personalization Workshop | workshop рад | https://www.cs.utexas.edu/~ai-lab/pub-view.php?PubID=51494 | верност насупрот уверљивости | 2.3 | Апстракт: примарна страница ауторске лабораторије UT Austin разликује promotion и satisfaction; PDF није прочитан |
| L17 | Ball, J.; Srinivasan, V. C. (1994), *Using the Analytic Hierarchy Process in house selection*, Journal of Real Estate Finance and Economics 9, 69–85 | housing/MCDM | 10.1007/BF01153589 | критеријуми избора куће | 2.4 | Апстракт: страница издавача Springer и Semantic Scholar; исправљени коаутор и наслов; ранија блокада отклоњена |
| L18 | Opoku, R. A.; Abdul-Muhmin, A. G. (2010), *Housing Preferences and Attribute Importance among Low-income Consumers in Saudi Arabia*, Habitat International 34(2), 219–227 | housing | 10.1016/j.habitatint.2009.09.006 | вишеструки housing атрибути | 2.4 | Апстракт: ScienceDirect издавачки приказ из претраге; OpenAlex бележи 2009 online, издавач 2010 том/број |
| L19 | Sirgy, M. J.; Grzeskowiak, S.; Su, C. (2005), *Explaining housing preference and choice: The role of self-congruity and functional congruity*, Journal of Housing and the Built Environment 20(4), 329–347 | housing | 10.1007/s10901-005-9020-7 | функционални и симболички аспекти избора дома | 2.4 | Апстракт: страница издавача Springer и институционални запис; исправљени часопис, наслов, стране и DOI |
| L20 | Brooke, J. (1996), *SUS: A “Quick and Dirty” Usability Scale*, у Usability Evaluation in Industry, 189–194 | инструмент | https://digital.ahrq.gov/sites/default/files/docs/survey/systemusabilityscale%2528sus%2529_comp%255B1%255D.pdf | SUS scoring | 2.5/5 | Пуни текст: AHRQ репродукција изворног поглавља са ставкама и поступком бодовања |
| L21 | Bangor, A.; Kortum, P. T.; Miller, J. T. (2008), *An Empirical Evaluation of the System Usability Scale*, IJHCI 24(6), 574–594 | SUS | 10.1080/10447310802205776 | тумачење SUS-а | 2.5 | Апстракт: страница издавача Taylor & Francis потврђује емпиријску процену и предложене описне оцене |
| L22 | Järvelin, K.; Kekäläinen, J. (2002), *Cumulated Gain-Based Evaluation of IR Techniques*, ACM TOIS 20(4), 422–446 | ranking метрике | 10.1145/582415.582418 | DCG/nDCG | 1.4/2.5 | Само метаподаци: OpenAlex DOI |
| L23 | Shani, G.; Gunawardana, A. (2011), *Evaluating Recommendation Systems*, у Recommender Systems Handbook, 257–297 | евалуација | 10.1007/978-0-387-85820-3_8 | offline/online мере | 2.5 | Апстракт: Springer издваја offline поређење, корисничке студије и велике online огледе; први online приказ 2010, библиографија књиге 2011 |
| L24 | Cohen, J. (1988), *Statistical Power Analysis for the Behavioral Sciences*, 2nd ed., Routledge | дизајн/ефекат | 10.4324/9780203771587 | effect size и ограничење моћи | 2.5/9 | Метаподаци: OpenAlex DOI електронског издања (2013); Routledge бележи copyright 1988 за 2. издање |

Напомена о садржају: сви записи су библиографски проверени, али за L1–L2,
L12, L14, L22 и L24 доступни су само метаподаци; опис књиге/садржај
за L4, L5 и L9 није исто што и читање поглавља. За закључке PIR-а користе
се само тврдње које подржава прочитани апстракт, издавачки опис или примарни
текст. Детаљни налази без таквог ослонца изостављени су из рукописа.
