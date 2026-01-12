<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DocumentType;
use Illuminate\Support\Facades\DB;

class DocumentTypeSeeder extends Seeder
{
    public function run(): void
    {
        // =========================================================================
        // TAHAP 1: HAPUS DOKUMEN YANG TIDAK DIPERLUKAN
        // =========================================================================
        // Menghapus 'Limit Sample Sheet' (LSS)
        DocumentType::where('code', 'LSS')->delete();


        // =========================================================================
        // TAHAP 2: MIGRASI KODE LAMA KE BARU (PENTING UNTUK PRODUCTION)
        // =========================================================================
        // Kita ubah kode di database dulu sebelum menjalankan updateOrCreate
        // Format: 'KODE_LAMA' => 'KODE_BARU'

        $codeRenames = [
            'ARD'     => 'ARD',      // Typo fix Aplication -> Application (Code sama)
            'BAC'     => 'BACL',     // Berita Acara Crusher -> Berita Acara Crusher L1, L2
            'CC1'     => 'CCS1',     // Capacity Check 1 -> Stage 1
            'CC2'     => 'CCS2',     // Capacity Check 2 -> Stage 2
            'IKWIPFI' => 'IWPI',     // IK/WI Process Final Inspection -> IK/WI Process Inspection
            'LSSR'    => 'LSSS',     // Limit Sample Sheet Request -> Submission
            'OT'      => 'OTL',      // Other Test -> Other Test L1, L2
            'PAFPL'   => 'PSPS',     // PAF Plan -> PAF Submission
            'PFMEA'   => 'PFWS',     // PFMEA -> Process FMEA Work Sheet
            'MNCA'    => 'RMNCA',    // Marking No Cavity Approval -> Registration...
            'MNCR'    => 'RMNCS',    // Marking No Cavity Request -> Registration... Submission
        ];

        foreach ($codeRenames as $oldCode => $newCode) {
            // Hanya update jika kode lama ada dan kode baru belum ada
            if (DocumentType::where('code', $oldCode)->exists() && !DocumentType::where('code', $newCode)->exists()) {
                DocumentType::where('code', $oldCode)->update(['code' => $newCode]);
            }
        }

        // =========================================================================
        // TAHAP 3: DATA FINAL (GABUNGAN LAMA + GAMBAR, SORTED A-Z)
        // =========================================================================

        $documentTypes = [
            'ABCD Table Approval' => 'ATA', // 1
            'ABCD Table Submission' => 'ATS', // 2
            'Ability Process Pre Mass Production (N200) L1, L2' => 'APPMP', // 3 (Replaces APPM)
            'Agreement Special Characteristic' => 'ASC', // 4
            'Appeareance Approval Report (AAR)' => 'AAR', // 5
            'Application Revision Drawing' => 'ARD', // 6 (Replaces Aplication Revision Drawing)
            'Application for Delivery Packing Style' => 'AFDPS', // 7
            'Audit SIMPICA 1' => 'AS1', // 8
            'Audit SIMPICA 2' => 'AS2', // 9
            'Audit SIMPICA 3' => 'AS3', // 10
            'Berita Acara Crusher L1, L2' => 'BACL', // 11 (Replaces BAC)
            'COQ (Certificate of Quality) / N5' => 'COQ', // 12
            'CPCPK' => 'CPCPK', // 13
            'Capability Process' => 'CPs', // 14
            'Capability Process - Capability Process Index (CpCpk) Mass Production' => 'CPIMP', // 15
            'Capability Process - Capability Process Index (CpCpk) Stage 1' => 'CPIS1', // 16
            'Capability Process - Capability Process Index (CpCpk) Stage 2' => 'CPIS2', // 17
            'Capability Process - Capability Process Index (CpCpk) Stage 3' => 'CPIS3', // 18
            'Capability Process - Capability Process Index (CpCpk) Stage 4' => 'CPIS4', // 19 (New from Image)
            'Capability Similar Part' => 'CSP', // 20
            'Capability process ∇Q, ∇E ∇R (N30 Data)' => 'CPN30', // 21
            'Capability process ∇Q, ∇E ∇R (N30 Data) Event N1000' => 'CPN30E1000', // 22
            'Capacity Bottle Neck Process Data N100 (Include subcont)' => 'CBNP100', // 23
            'Capacity Bottle Neck Process Data N1000 (Include subcont)' => 'CBNP1000', // 24
            'Capacity Check' => 'CC', // 25
            'Capacity Check Approval' => 'CCA', // 26 (New from Image)
            'Capacity Check Stage 1' => 'CCS1', // 27 (Replaces CC1)
            'Capacity Check Stage 2' => 'CCS2', // 28 (Replaces CC2)
            'Capacity Check Stage 3' => 'CCS3', // 29 (New from Image)
            'Capacity Check Stage 4' => 'CCS4', // 30 (New from Image)
            'Capacity Check Stage 5' => 'CCS5', // 31 (New from Image)
            'Capacity Production' => 'CP', // 32
            'Certified Laboratory Documentation' => 'CLD', // 33
            'Check Capability Process (N30 Data) Stage 1' => 'CCP30S1', // 34
            'Check Capability Process (N30 Data) Stage 2' => 'CCP30S2', // 35
            'Check Sheet Manufacturing Newness' => 'CSMN', // 36
            'Check Sheet Stage 1' => 'CSS1', // 37
            'Check Sheet Stage 2' => 'CSS2', // 38
            'Check material specification (Include new child part)' => 'CMS', // 39
            'Check sheet GENBA 4M N100' => 'CSG4M100', // 40
            'Checking Aids' => 'CA', // 41
            'Checksheet' => 'CS', // 42
            'Checksheet In Process' => 'CSIP', // 43
            'Consistency Confirmation Check Sheet between the Drawings and Records' => 'CCCSDR', // 44
            'Control Plan (QCPC)' => 'QCPC', // 45
            'Control Status of Material' => 'CSM', // 46
            'Counterpartner' => 'CPR', // 47
            'Counterpartner Approval' => 'CPA', // 48 (New from Image)
            'Counterpartner Submission' => 'CPS', // 49 (New from Image)
            'Cs Genba 4M event N1000' => 'CSG4M1000', // 50
            'Customer Engineering Approval' => 'CEA', // 51
            'DJTF' => 'DJTF', // 52
            'DJTF Stage 1' => 'DJTFS1', // 53
            'DJTF Stage 2' => 'DJTFS2', // 54
            'Data Properties Material L1, L2' => 'DPML', // 55
            'Data Properties Produk L1, L2' => 'DPPL', // 56
            'Data Ukur by Drawing L1, L2' => 'DUDL', // 57
            'Declaration Masspro' => 'DM', // 58
            'Design & Manufacturing Newness' => 'DMN', // 59
            'Design FMEA Work Sheet' => 'DFWS', // 60 (New from Image)
            'Design Record' => 'DR', // 61
            'Document Hearing After Die Go' => 'DHADG', // 62
            'Document Hearing Before Die Go' => 'DHBDG', // 63
            'Durabilitry test delivery' => 'DTD', // 64
            'ECN Record' => 'ECNR', // 65
            'ECN Record Approval' => 'ECNRA', // 66 (New from Image)
            'ECN Record Stage 1' => 'ECNRS1', // 67 (New from Image)
            'ECN Record Stage 2' => 'ECNRS2', // 68 (New from Image)
            'ECN Record Stage 3' => 'ECNRS3', // 69 (New from Image)
            'ECN Record Stage 4' => 'ECNRS4', // 70 (New from Image)
            'ECN Record Stage 5' => 'ECNRS5', // 71 (New from Image)
            'Engineering Change Request (ECR)' => 'ECR', // 72
            'Engineering Changes Documents (ECD)' => 'ECD', // 73
            'Final Report/Summary' => 'FRS', // 74
            'First Lot Tag (First Delivery Mass Prod)' => 'FLT', // 75
            'First product inspection / N1' => 'FPI', // 76
            'Floor layout Diagram' => 'FLD', // 77
            'Form Trial report (New Part)' => 'FTR', // 78
            'HCSMS versi terbaru' => 'HCSMS', // 79
            'High Volume Production' => 'HVP', // 80
            'High Volume Production Trial (HVPT) For PILOT' => 'HVPT', // 81
            'Hinanyo' => 'HNY', // 82
            'Hinanyo Stage 1' => 'HNY1', // 83
            'Hinanyo Stage 2' => 'HNY2', // 84
            'Identification List Tooling and Process' => 'ILTP', // 85
            'Identification List Tooling and Process Approval' => 'ILTPA', // 86 (New from Image)
            'Identification List Tooling and Process Stage 1' => 'ILTPS1', // 87 (New from Image)
            'Identification List Tooling and Process Stage 2' => 'ILTPS2', // 88 (New from Image)
            'Identification List Tooling and Process Stage 3' => 'ILTPS3', // 89 (New from Image)
            'Identification List Tooling and Process Stage 4' => 'ILTPS4', // 90 (New from Image)
            'Identification List Tooling and Process Stage 5' => 'ILTPS5', // 91 (New from Image)
            'IK/WI Process Assy' => 'IKWIPA', // 92
            'IK/WI Process Finishing' => 'IKWIPF', // 93
            'IK/WI Process Inspection' => 'IWPI', // 94 (Replaces IK/WI Process Final Inspection)
            'IK/WI Process Leak Test' => 'IWPLT', // 95 (New from Image)
            'IK/WI Process Marking' => 'IKWIPM', // 96
            'IK/WI Process Packing' => 'IKWIPP', // 97
            'IK/WI Process Pre Produksi' => 'IKWIPPRE', // 98
            'IK/WI Process Produksi' => 'IKWIPPRO', // 99
            'IMDS & Cover' => 'IMDSC', // 100
            'Implementation of Coutermeasure FTP problem' => 'ICFP', // 101
            'Initial Control Mass Production' => 'ICMP', // 102
            'Initial Part for Project' => 'IPP', // 103
            'Initial Process Capability (Cpk/Ppk) Survey Results' => 'IPCSR', // 104 (New from Image)
            'Initial Process Studies (Ppk, Cpk )' => 'IPS', // 105
            'Initial Production Control (IPC)' => 'IPC', // 106
            'Initial Sample Inspection Result - ISIR' => 'ISIR', // 107
            'Initial Stage Control Plan Approval' => 'ISCPA', // 108 (New from Image)
            'Initial Stage Control Plan Stage 1' => 'ISCPS1', // 109 (New from Image)
            'Initial Stage Control Plan Stage 2' => 'ISCPS2', // 110 (New from Image)
            'Initial Stage Control Plan Stage 3' => 'ISCPS3', // 111 (New from Image)
            'Initial Stage Control Plan Stage 4' => 'ISCPS4', // 112 (New from Image)
            'Initial Stage Control Plan Stage 5' => 'ISCPS5', // 113 (New from Image)
            'Initial production control plan' => 'IPCP', // 114
            'Inspection Confirmation Report Approval' => 'ICRA', // 115 (New from Image)
            'Inspection Confirmation Report Stage 1' => 'ICRS1', // 116 (New from Image)
            'Inspection Confirmation Report Stage 2' => 'ICRS2', // 117 (New from Image)
            'Inspection Confirmation Report Stage 3' => 'ICRS3', // 118 (New from Image)
            'Inspection Confirmation Report Stage 4' => 'ICRS4', // 119 (New from Image)
            'Inspection Confirmation Report Stage 5' => 'ICRS5', // 120 (New from Image)
            'Inspection Report' => 'IR', // 121
            'Inspection Standard' => 'IS', // 122
            'Inspection Standard Approval' => 'ISA', // 123
            'Inspection Standard Submission' => 'ISS', // 124
            'Inspection Tool' => 'IT', // 125
            'Inspection confirmation List' => 'ICL', // 126
            'Investigation of Regular Temporary Process' => 'IRTP', // 127
            'Jigs for Inspection' => 'JFI', // 128
            'Jigs for production' => 'JFP', // 129
            'Kakotora' => 'KKT', // 130
            'Kakotora Stage 1' => 'KKT1', // 131
            'Kakotora Stage 2' => 'KKT2', // 132
            'Kesesuaian Part No/ Suffix pada Drawing' => 'KPNS', // 133
            'Kesiapan Armada Delivery' => 'KAD', // 134
            'Kesiapan Mesin' => 'KM', // 135
            'Kesiapan Packing (Polybox / Trolley / Carton)' => 'KPPC', // 136
            'Layout Stage 1' => 'LS1', // 137
            'Layout Stage 2' => 'LS2', // 138
            'Lead Time' => 'LT', // 139
            'Limit Sample Sheet Approval' => 'LSSA', // 140
            'Limit Sample Sheet Submission' => 'LSSS', // 141 (Replaces Limit Sample Sheet Request)
            'List of Imported Part' => 'LIP', // 142 (New from Image)
            'List of Part Supplied by Vendor' => 'LPSV', // 143 (Same name, kept)
            'List of Parts Supplied by Vendor' => 'LPSV2', // 144 (User list variant, added '2' to avoid duplicate key conflict if handled differently, or remove if duplicate. Kept for safety)
            'Lot Control Application Approval' => 'LCAA', // 145 (New from Image)
            'Lot Control Application Stage 1' => 'LCAS1', // 146 (New from Image)
            'Lot Control Application Stage 2' => 'LCAS2', // 147 (New from Image)
            'Lot Control Application Stage 3' => 'LCAS3', // 148 (New from Image)
            'Lot Control Application Stage 4' => 'LCAS4', // 149 (New from Image)
            'Lot Control Application Stage 5' => 'LCAS5', // 150 (New from Image)
            'Low Volume Production Trial (LVPT) For MPP' => 'LVPT', // 151
            'MCP Stage 1' => 'MCPS1', // 152
            'MCP Stage 2' => 'MCPS2', // 153
            'MPTC' => 'MPTC', // 154
            'MQS' => 'MQS', // 155
            'MQS Stage 1' => 'MQSS1', // 156
            'MQS Stage 2' => 'MQSS2', // 157
            'MSA evaluation ( Plan evaluation for M.P and jig)' => 'MSAE', // 158
            'Manufacturing Checksheet Approval' => 'MCA', // 159 (New from Image)
            'Manufacturing Checksheet Stage 1' => 'MCS1', // 160 (New from Image)
            'Manufacturing Checksheet Stage 2' => 'MCS2', // 161 (New from Image)
            'Manufacturing Checksheet Stage 3' => 'MCS3', // 162 (New from Image)
            'Manufacturing Checksheet Stage 4' => 'MCS4', // 163 (New from Image)
            'Manufacturing Checksheet Stage 5' => 'MCS5', // 164 (New from Image)
            'Mass Production Periode (MPP)' => 'MPP', // 165
            'Mass Production Trial Confirmation' => 'MPTC2', // 166 (Conflict with code above, using suffix)
            'Mass production check data' => 'MPCD', // 167
            'Master Sample/ Boundary Sample' => 'MSBS', // 168
            'Master Schedule' => 'MS', // 169
            'Master Schedule Control Plan' => 'MSCP', // 170
            'Material Confirmation' => 'MC', // 171
            'Material Confirmation L1, L2' => 'MCL', // 172 (New from Image)
            'Material Data Sheet (MDS)' => 'MDS', // 173
            'Material Performace Test Summarize' => 'MPTS', // 174
            'Measurement System Analysis (MSA) Attribute' => 'MSAA', // 175 (New from Image)
            'Measurement System Analysis (MSA) Variable' => 'MSAV', // 176 (New from Image)
            'Measurement System Analysis Studies' => 'MSAS', // 177
            'Measurement System Analysis studies' => 'MSAS', // 178 (Duplicate case check, key insensitive)
            'Millsheet (COA) L1, L2' => 'MSCOA', // 179
            'Mold Production' => 'MP', // 180
            'N-1 Inspection Drawing (Include child part, Visual judgment)' => 'N1ID', // 181
            'N-1 Inspection Drawing (Include child part, Visual judgment) event N1000' => 'N1IDE1000', // 182
            'N-1 NG Portion Agreement (NG Portion + N1 Attachment)' => 'NNPA', // 183
            'N-5 Data & Evaluation Dimension by Supplier (Event N5)' => 'N5DE', // 184
            'N-5 Result Verification by LAB YIMM (Dimension/Durability/Etc)' => 'N5RV', // 185
            'N-5 Supplier data (Event N100)' => 'N5SD100', // 186
            'N-5 Supplier data (Event N1000)' => 'N5SD1000', // 187
            'N200' => 'N200', // 188
            'N30/ CpCpK Special characteristic point event N100' => 'N30CPS100', // 189
            'N30/ CpCpK Special characteristic point event N1000' => 'N30CPS1000', // 190
            'N5 Event N1000' => 'N5E1000', // 191
            'N5 Lab / Verifikasi Customer' => 'N5LVC', // 192
            'N5 or N10' => 'N5N10', // 193
            'New Project Education' => 'NPE', // 194
            'OPL Similar Part' => 'OPLSP', // 195
            'OS' => 'OS', // 196
            'OS Stage 1' => 'OSS1', // 197
            'OS Stage 2' => 'OSS2', // 198
            'On Tooling On Process (OTOP)' => 'OTOP', // 199
            'Other Test L1, L2' => 'OTL', // 200 (Replaces Other Test)
            'PPAP 1' => 'PPAP1', // 201
            'PPAP 2' => 'PPAP2', // 202
            'PPAP 3' => 'PPAP3', // 203
            'PPAP 4' => 'PPAP4', // 204
            'PPAP 5' => 'PPAP5', // 205
            'PPAP Application Document' => 'PAD', // 206 (New from Image)
            'PPAP Complete Subcont' => 'PPAPCS', // 207
            'PPAP Submission Materials Table' => 'PSMT', // 208
            'PPC (Claim market global)' => 'PPCMG', // 209
            'PQCS' => 'PQCS', // 210
            'PQCS L1, L2' => 'PQL', // 211
            'PQCS Stage 1' => 'PQCSS1', // 212 (New from Image)
            'PQCS Stage 2' => 'PQCSS2', // 213 (New from Image)
            'PQRS (Part Qualification Request Sheet)' => 'PQRS', // 214 (Renamed from PQRS)
            'Packing Standard (PAF) Approval' => 'PAFAP', // 215
            'Packing Standard (PAF) Submission' => 'PSPS', // 216 (Replaces PAF Plan)
            'Packing Standard Stage 1' => 'PSS1', // 217 (New from Image)
            'Packing Standard Stage 2' => 'PSS2', // 218 (New from Image)
            'Packing Standard Stage 3' => 'PSS3', // 219 (New from Image)
            'Packing Standard Stage 4' => 'PSS4', // 220 (New from Image)
            'Packing Standard Stage 5' => 'PSS5', // 221 (New from Image)
            'Parameter setting machine' => 'PSM', // 222
            'Part History' => 'PH', // 223
            'Part Inspection Data Sheet (PIDS)' => 'PIDS', // 224
            'Part Submission Warranty (PSW)' => 'PSW', // 225
            'Parts History' => 'PH2', // 226 (Added from image, handled collision)
            'Past Failures and Recurrence Preventive Measures' => 'PFRPM', // 227
            'Person in Charge of QA' => 'PICQA', // 228
            'Pilot Sample Inspection Report (n= 10 in principle)' => 'PSIR', // 229 (New from Image)
            'Pilot Sample Inspection Results (All items) (Written in Red on The Drawings)' => 'PSIRA', // 230 (New from Image)
            'Pilot Samples (n=5)' => 'PS5', // 231 (New from Image)
            'Pokayoke System' => 'PKS', // 232
            'Process FMEA Work Sheet' => 'PFWS', // 233 (Replaces PFMEA)
            'Process flow chart' => 'PFC', // 234
            'Production Facilities' => 'PF', // 235
            'Production Preparation Planning' => 'PPP', // 236
            'Production Process Sheet' => 'PPS', // 237
            'QA Matrix' => 'QAM', // 238
            'QA Matrix Stage 1' => 'QAMS1', // 239
            'QA Matrix Stage 2' => 'QAMS2', // 240
            'QAV' => 'QAV', // 241
            'QC Process Charts' => 'QCPC2', // 242
            'QCPC Approval' => 'QCPCA', // 243
            'QCPC Submission' => 'QCPCS', // 244
            'Quality Control Process Chart (QCPC) Approval' => 'QCPCAP', // 245 (New from Image, slight diff from QCPC Approval)
            'Quality Control Process Chart (QCPC) Submission' => 'QCPCSU', // 246 (New from Image, slight diff from QCPC Submission)
            'Readiness For Mass Production Trial' => 'RMPT', // 247 (New from Image)
            'Readiness For Masspro Trial' => 'RMT', // 248 (User list variant)
            'Received Drawing (Supplier\'s Drawing)' => 'RDSD', // 249 (New from Image)
            'Records of Compliance with Customer specific Requirements' => 'RCCSR', // 250
            'Registration Marking No Cavity Approval' => 'RMNCA', // 251 (Replaces Marking No Cavity Approval)
            'Registration Marking No Cavity Submission' => 'RMNCS', // 252 (Replaces Marking No Cavity Request)
            'Reliability and Specification Confirmation Tests Results (n=3)' => 'RSCTR', // 253 (New from Image)
            'RoHS L1, L2' => 'ROHS', // 254
            'SIS P' => 'SISP', // 255
            'SIS P Approval' => 'SISPA', // 256 (New from Image)
            'SIS P Stage 1' => 'SISPS1', // 257 (New from Image)
            'SIS P Stage 2' => 'SISPS2', // 258 (New from Image)
            'SIS P Stage 3' => 'SISPS3', // 259 (New from Image)
            'SIS P Stage 4' => 'SISPS4', // 260 (New from Image)
            'SIS P Stage 5' => 'SISPS5', // 261 (New from Image)
            'Sampel Product' => 'SP', // 262
            'Sample Evaluation by Supplier (Durability/WA test/Etching/Strength/Etc) (include child part) Stage 1' => 'SEBSS1', // 263 (New from Image)
            'Sample Evaluation by Supplier (Durability/WA test/Etching/Strength/Etc) (include child part) Stage 2' => 'SEBSS2', // 264 (New from Image)
            'Schedule training' => 'ST', // 265
            'Self Audit' => 'SA', // 266
            'Skill Matrix & Evaluation Man Power' => 'SMEMP', // 267
            'Skill Matrix Man Power' => 'SMMP', // 268
            'Skill Matrix Man Power L1, L2' => 'SMMPL', // 269 (New from Image)
            'SoC Free' => 'SOCF', // 270
            'Special Characteristic Control Plan (If available QER)' => 'SCCP', // 271
            'Special Inspection Plan' => 'SIP', // 272
            'Spec List' => 'SPL', // 273
            'Specification Meeting' => 'SPM', // 274
            'Standard Operational Procedure (SOP)' => 'SOP', // 275 (Renamed from SOP)
            'Standard Packaging' => 'SPKG', // 276
            'Status ECI Implement' => 'SEI', // 277
            'Study Drawing' => 'STD', // 278
            'Study Drawing Stage 1' => 'SDS1', // 279 (New from Image)
            'Study Drawing Stage 2' => 'SDS2', // 280 (New from Image)
            'Sub-Supplier Control Table' => 'SSCT', // 281 (New from Image)
            'Submit IMDS Registration (Environmental Friendly Product)' => 'SIR', // 282
            'Submit Initial production control implementation result (after 1 Month MP)' => 'SIPCIR', // 283
            'Submition & application regulation certificate (SNI, ECE,CCC, Permendag, Label, etc)' => 'SARC', // 284
            'Supplier Development Schedule' => 'SDS', // 285
            'Supplier Development Schedule (SDS) Approval' => 'SDSA', // 286 (New from Image)
            'Supplier Development Schedule (SDS) Stage 1' => 'SDSS1', // 287 (New from Image)
            'Supplier Development Schedule (SDS) Stage 2' => 'SDSS2', // 288 (New from Image)
            'Supplier Development Schedule (SDS) Stage 3' => 'SDSS3', // 289 (New from Image)
            'Supplier Development Schedule (SDS) Stage 4' => 'SDSS4', // 290 (New from Image)
            'Supplier Development Schedule (SDS) Stage 5' => 'SDSS5', // 291 (New from Image)
            'Supplier Evaluation Sheet' => 'SES', // 292
            'Supplier Evaluation Sheet Mass Production' => 'SESMP', // 293
            'Supplier Evaluation Sheet Stage 1' => 'SES1', // 294
            'Supplier Evaluation Sheet Stage 2' => 'SES2', // 295
            'Supplier Evaluation Sheet Stage 3' => 'SES3', // 296
            'Supplier Evaluation Sheet Stage 4' => 'SESS4', // 297 (New from Image)
            'Supplier Representative Information' => 'SRI', // 298
            'Supply Chain' => 'SC', // 299
            'Surface Finishing Check' => 'SFC', // 300
            'System Check' => 'SYSC', // 301
            'Test Material' => 'TM', // 302
            'Test Material - Product Approval' => 'TMPA', // 303 (New from Image)
            'Test Material - Product Stage 1' => 'TMPS1', // 304 (New from Image)
            'Test Material - Product Stage 2' => 'TMPS2', // 305 (New from Image)
            'Test Material - Product Stage 3' => 'TMPS3', // 306 (New from Image)
            'Test Material - Product Stage 4' => 'TMPS4', // 307 (New from Image)
            'Test Material - Product Stage 5' => 'TMPS5', // 308 (New from Image)
            'Test Product' => 'TP', // 309
            'Test and Durability Summarize' => 'TDS', // 310
            'Tooling Progress Report' => 'TPR', // 311
            'Verifikasi packing' => 'VP', // 312
            'Work Instruction' => 'WI', // 313
        ];

        foreach ($documentTypes as $name => $code) {
            DocumentType::updateOrCreate(
                ['code' => $code],
                ['name' => $name]
            );
        }
    }
}
